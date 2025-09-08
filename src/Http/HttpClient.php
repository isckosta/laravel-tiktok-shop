<?php

namespace TikTokShop\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use TikTokShop\Support\Signer;
use TikTokShop\Repositories\CredentialsRepositoryInterface;
use TikTokShop\Auth\TokenService;

/**
 * HTTP Client para comunicação autenticada com a API da TikTok Shop.
 *
 * Responsável por:
 * - Gerenciar tokens de acesso e refresh.
 * - Construir queries assinadas.
 * - Enviar requisições GET/POST/PUT/DELETE.
 */
class HttpClient
{
    private const HEADER_REQUEST_ID   = 'X-Request-Id';
    private const HEADER_ACCESS_TOKEN = 'x-tts-access-token';
    private const STATUS_UNAUTHORIZED = 401;
    private const STATUS_FORBIDDEN    = 403;

    private PendingRequest $http;
    private ?string $shopCipher = null;
    private string $baseUri;

    public function __construct(
        string $baseUri,
        int $timeout,
        private string $appKey,
        private string $appSecret,
        private ?string $accessToken = null,
        ?HttpFactory $factory = null,
        private ?CredentialsRepositoryInterface $credsRepo = null,
        private ?TokenService $tokens = null,
        private ?string $clientHash = null,
    ) {
        $this->baseUri = rtrim($baseUri, '/');

        $this->http = Http::baseUrl($this->baseUri)
            ->timeout($timeout)
            ->withHeaders([self::HEADER_REQUEST_ID => (string) Str::uuid()]);
    }

    public function get(string $uri, array $query = [])
    {
        $this->ensureFreshToken();
        return $this->http->withToken($this->accessToken)->get($uri, $query);
    }

    public function post(string $uri, array $payload = [])
    {
        $this->ensureFreshToken();

        \Log::debug('[TikTokShop] POST sem assinatura', [
            'token'   => $this->accessToken,
            'payload' => $payload,
        ]);

        return $this->http
            ->withToken($this->accessToken)
            ->asJson()
            ->post($uri, $payload);
    }

    public function getWithAuth(string $uri, array $queryParams = [], bool $omitShopCipher = false)
    {
        return $this->requestWithAuth('get', $uri, [], $queryParams, false, $omitShopCipher);
    }

    public function postWithAuth(
        string $uri,
        array $body = [],
        array $queryParams = [],
        bool $omitShopCipher = false
    ) {
        return $this->requestWithAuth('post', $uri, $body, $queryParams, false, $omitShopCipher);
    }

    public function putWithAuth(string $uri, array $body = [], array $queryParams = [])
    {
        return $this->requestWithAuth('put', $uri, $body, $queryParams);
    }

    public function deleteWithAuth(
        string $uri,
        array $body = [],
        array $queryParams = [],
        bool $omitShopCipher = false
    ) {
        return $this->requestWithAuth('delete', $uri, $body, $queryParams, false, $omitShopCipher);
    }

    public function postMultipartWithAuth(string $uri, array $parts = [], array $queryParams = [])
    {
        return $this->requestWithAuth('post', $uri, $parts, $queryParams, true, true);
    }

    private function requestWithAuth(
        string $method,
        string $uri,
        array $body = [],
        array $queryParams = [],
        bool $isMultipart = false,
        bool $omitShopCipher = false
    ) {
        $this->ensureFreshToken();

        $query = $this->buildSignedQuery(
            uri: $uri,
            query: $queryParams,
            body: $isMultipart ? [] : $body,
            isMultipart: $isMultipart,
            omitShopCipher: $omitShopCipher
        );

        $request = $this->http->withHeaders([self::HEADER_ACCESS_TOKEN => $this->accessToken])
            ->withOptions(['query' => $query]);

        if ($isMultipart) {
            return $request->asMultipart()->{$method}($uri, $body);
        }

        if (in_array($method, ['post', 'put', 'delete'], true)) {
            $request = $request->withBody(
                json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'application/json'
            );
        }

        return $request->{$method}($uri);
    }

    private function ensureFreshToken(): void
    {
        if (!$this->credsRepo || !$this->clientHash) {
            return;
        }

        $cred = $this->credsRepo->findByClientHash($this->clientHash);
        if (!$cred) {
            return;
        }

        $this->shopCipher  = $cred->shop_cipher ?? $this->shopCipher;
        $this->accessToken = $cred->access_token ?? $this->accessToken;

        if ($this->shouldRefreshToken($cred)) {
            $this->refreshTokenAndPersist();
        }
    }

    private function shouldRefreshToken($cred): bool
    {
        return $cred->refresh_token && $this->tokens && $cred->access_token_expires_at
            && now()->greaterThanOrEqualTo($cred->access_token_expires_at);
    }

    private function refreshTokenAndPersist(): void
    {
        $cred = $this->credsRepo?->findByClientHash($this->clientHash);
        if (!$cred) {
            return;
        }

        $result = $this->tokens->refresh($cred->refresh_token);

        $this->credsRepo->updateTokens($this->clientHash, [
            'access_token'            => $result->accessToken,
            'refresh_token'           => $result->refreshToken,
            'access_token_expires_at' => $result->accessTokenExpiresAt,
            'open_id'                 => $result->openId,
            'scopes'                  => $result->scopes,
        ]);

        $this->accessToken = $result->accessToken;
        $this->shopCipher  = $cred->shop_cipher ?: $this->shopCipher;
    }

    private function buildSignedQuery(
        string $uri,
        array $query = [],
        array $body = [],
        bool $isMultipart = false,
        bool $omitShopCipher = false
    ): array {
        if (!$omitShopCipher && empty($query['shop_cipher']) && $this->shopCipher) {
            $query['shop_cipher'] = $this->shopCipher;
        }

        $query['app_key']   = $this->appKey;
        $query['timestamp'] = (string) time();

        $query = $this->normalizeArrayParams($query);

        $pathname = '/' . ltrim($uri, '/');
        $sign     = Signer::signOpenApi(
            pathname: $pathname,
            query: $query,
            body: $body,
            appSecret: $this->appSecret,
            isMultipart: $isMultipart
        );

        $query['sign'] = $sign;

        \Log::debug('[TikTokShop][HttpClient] Assinatura gerada', compact('pathname', 'query', 'body', 'sign'));

        return $query;
    }

    private function normalizeArrayParams(array $params): array
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        return $params;
    }
}
