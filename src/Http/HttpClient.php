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

class HttpClient
{
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
            ->withHeaders([
                'X-Request-Id' => (string) Str::uuid(),
            ]);
    }

    public function get(string $path, array $query = [])
    {
        $this->ensureFreshToken();

        return $this->http
            ->withToken($this->accessToken)
            ->get($path, $query);
    }

    public function post(string $path, array $payload = [])
    {
        $this->ensureFreshToken();

        \Log::debug('Request TikTok', [
            'access_token' => $this->accessToken,
            'payload' => $payload,
            'headers' => $this->http->getOptions()
        ]);

        return $this->http
            ->withToken($this->accessToken)
            ->asJson()
            ->post($path, $payload);
    }

    public function postMultipart(string $path, array $parts = [])
    {
        $this->ensureFreshToken();

        return $this->http
            ->withToken($this->accessToken)
            ->asMultipart()
            ->post($path, $parts);
    }

    private function sendWithRetry(string $method, string $path, array $data, bool $multipart = false)
    {
        try {
            return $this->send($method, $path, $data, $multipart);
        } catch (RequestException $e) {
            if ($this->shouldTryRefresh($e)) {
                $this->refreshTokenAndPersist();
                return $this->send($method, $path, $data, $multipart);
            }

            throw $e;
        }
    }

    private function send(string $method, string $path, array $data, bool $multipart = false)
    {
        if ($multipart) {
            return $this->http->asMultipart()->{$method}($path, $data);
        }

        if ($method === 'postJson') {
            return $this->http->asJson()->post($path, $data);
        }

        return $this->http->{$method}($path, $data);
    }

    private function ensureFreshToken(): void
    {
        // se tiver repositório e clientHash, pegue as credenciais
        if ($this->credsRepo && $this->clientHash) {
            $cred = $this->credsRepo->findByClientHash($this->clientHash);

            // sempre tenta popular shop_cipher
            if ($cred && $cred->shop_cipher) {
                $this->shopCipher = $cred->shop_cipher;
            }

            // popula access_token atual (mesmo sem refresh)
            if ($cred && $cred->access_token) {
                $this->accessToken = $cred->access_token;
            }

            // só tenta refresh se tiver token service + refresh_token
            if ($cred && $this->tokens && $cred->refresh_token) {
                $expiresAt = $cred->access_token_expires_at;
                if ($expiresAt && now()->greaterThanOrEqualTo($expiresAt)) {
                    $this->refreshTokenAndPersist();
                }
            }
        }
    }

    private function refreshTokenAndPersist(): void
    {
        $cred = $this->credsRepo->findByClientHash($this->clientHash);
        if (! $cred) {
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

        // garantir que continuamos com o shop_cipher carregado do banco
        $this->shopCipher = $cred->shop_cipher ?: $this->shopCipher;
    }

    private function shouldTryRefresh(RequestException $e): bool
    {
        $status = $e->response?->status();
        return in_array($status, [401, 403], true)
            && $this->tokens && $this->credsRepo && $this->clientHash;
    }

    private function sign(array $params): array
    {
        unset($params['sign']);

        // Apenas os parâmetros que vão na QUERY entram na assinatura:
        $params['app_key']   = $this->appKey;
        $params['timestamp'] = (string) now('UTC')->floorSecond()->timestamp;

        // NUNCA adicionar access_token aqui.
        // NUNCA adicionar parâmetros do BODY aqui.

        $params['sign'] = \TikTokShop\Support\Signer::sign($params, $this->appSecret);

        return $params;
    }

    public function postWithAuth(string $uri, array $json = [], array $queryParams = [])
    {
        $this->ensureFreshToken();

        $query = $this->buildSignedQuery(
            uri: $uri,
            query: $queryParams,
            body: $json,
            isMultipart: false
        );

        return $this->http
            ->withHeaders(['x-tts-access-token' => $this->accessToken])
            ->withOptions(['query' => $query])
            ->withBody(
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'application/json'
            )
            ->post($uri);
    }

    public function getWithAuth(string $uri, array $queryParams = []): \Illuminate\Http\Client\Response
    {
        $this->ensureFreshToken();

        $query = $this->buildSignedQuery(
            uri: $uri,
            query: $queryParams,
            body: [],
            isMultipart: false
        );

        return $this->http
            ->withHeaders(['x-tts-access-token' => $this->accessToken])
            ->withOptions(['query' => $query])
            ->get($uri);
    }
    private function buildUrl(string $uri): string
    {
        return $this->baseUri . '/' . ltrim($uri, '/');
    }

    private function buildSignedQuery(string $uri, array $query = [], array $body = [], bool $isMultipart = false): array
    {
        if ((empty($query['shop_cipher'])) && $this->shopCipher) {
            $query['shop_cipher'] = $this->shopCipher;
        }

        $query['app_key']   = $this->appKey;
        $query['timestamp'] = (string) time();

        $pathname = '/' . ltrim($uri, '/');

        $sign = Signer::signOpenApi(
            pathname: $pathname,
            query:    $query,
            body:     $body,
            appSecret:$this->appSecret,
            isMultipart: $isMultipart
        );

        $query['sign'] = $sign;

        \Log::debug('[TikTokShop][CreateProduct] Assinatura', [
            'pathname' => $pathname,
            'query'    => $query,
            'body'     => $body,
            'json'     => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'sign'     => $sign,
        ]);

        return $query;
    }
}
