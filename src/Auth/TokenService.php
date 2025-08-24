<?php

namespace TikTokShop\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TikTokShop\Models\TikTokShopCredential;
use TikTokShop\Repositories\CredentialsRepositoryInterface;
use TikTokShop\Auth\TokenResult;
use TikTokShop\Support\Signer;

class TokenService
{
    public function __construct(private CredentialsRepositoryInterface $repo) {}

    /**
     * Troca auth_code por tokens e obtém shop_cipher.
     */
    public function exchangeAuthorizedCode(string $authCode): TokenResult
    {
        $resp = Http::get('https://auth.tiktok-shops.com/api/v2/token/get', [
            'grant_type' => 'authorized_code',
            'auth_code'  => $authCode,
            'app_key'    => config('tiktokshop.auth.app_key'),
            'app_secret' => config('tiktokshop.auth.app_secret'),
        ])->json();

        Log::info('Resposta da API de autenticação TikTok Shop', ['response' => $resp]);

        if (! isset($resp['data']['access_token'])) {
            throw new \RuntimeException('Falha ao obter access_token: ' . json_encode($resp));
        }

        $data = $resp['data'];
        $accessToken = $data['access_token'];

        // 🔹 Buscar o shop_cipher obrigatoriamente via /authorization/shops
        $authorizedShops = $this->fetchAuthorizedShopsFromApi($accessToken);
        $shopCipher = $authorizedShops[0]['cipher'] ?? null;

        if (! $shopCipher) {
            Log::error('Nenhum shop_cipher retornado pelo Get Authorized Shops API', [
                'response' => $authorizedShops,
            ]);
            throw new \RuntimeException('shop_cipher não retornado pela TikTok Shop.');
        }

        return $this->mapTokenResult(array_merge($data, [
            'shop_cipher_resolved' => $shopCipher,
        ]));
    }

    /**
     * Atualiza o token usando refresh_token.
     */
    public function refresh(string $refreshToken): TokenResult
    {
        $resp = Http::get('https://auth.tiktok-shops.com/api/v2/token/refresh', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'app_key'       => config('tiktokshop.auth.app_key'),
            'app_secret'    => config('tiktokshop.auth.app_secret'),
        ])->throw()->json();

        return $this->mapTokenResult($resp['data'] ?? []);
    }

    /**
     * Salva ou atualiza credenciais no banco.
     */
    public function storeOrUpdate(string $clientHash, TokenResult $token): void
    {
        $data = [
            'app_key'                 => config('tiktokshop.auth.app_key'),
            'app_secret'              => config('tiktokshop.auth.app_secret'),
            'shop_cipher'             => $token->shopCipher,
            'access_token'            => $token->accessToken,
            'refresh_token'           => $token->refreshToken,
            'access_token_expires_at' => $token->accessTokenExpiresAt,
            'scopes'                  => $token->scopes,
        ];

        $existing = $this->repo->findByClientHash($clientHash);
        if ($existing) {
            $existing->fill($data)->save();
        } else {
            TikTokShopCredential::create(array_merge(['client_hash' => $clientHash], $data));
        }
    }

    /**
     * Obtém lojas autorizadas (para extrair o cipher).
     */
    private function fetchAuthorizedShopsFromApi(string $accessToken): array
    {
        $appKey = config('tiktokshop.auth.app_key');
        $appSecret = config('tiktokshop.auth.app_secret');
        $timestamp = time();
        $pathname = '/authorization/202309/shops';

        $query = [
            'app_key'   => $appKey,
            'timestamp' => $timestamp,
        ];
        $body = []; // GET não tem body

        // Usa Signer pra gerar a assinatura corretamente
        $sign = Signer::signOpenApi($pathname, $query, $body, $appSecret);

        $response = Http::withHeaders([
            'x-tts-access-token' => $accessToken,
            'Content-Type'       => 'application/json',
        ])->get("https://open-api.tiktokglobalshop.com{$pathname}", array_merge($query, ['sign' => $sign]));

        if ($response->failed()) {
            \Log::error('Falha ao buscar lojas autorizadas', [
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);
            return [];
        }

        return $response->json('data.shops') ?? [];
    }

    /**
     * Converte resposta em DTO TokenResult.
     */
    private function mapTokenResult(array $data): TokenResult
    {
        $accessToken = $data['access_token'] ?? null;
        $refreshToken = $data['refresh_token'] ?? null;

        $accessExpires = now()->addSeconds((int)($data['access_token_expire_in'] ?? 7200));
        $refreshExpires = isset($data['refresh_token_expire_in'])
            ? now()->addSeconds((int)$data['refresh_token_expire_in'])
            : null;

        return new TokenResult(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            accessTokenExpiresAt: $accessExpires,
            refreshTokenExpiresAt: $refreshExpires,
            openId: $data['open_id'] ?? null,
            shopCipher: $data['shop_cipher_resolved'] ?? null,
            sellerName: $data['seller_name'] ?? null,
            sellerRegion: $data['seller_base_region'] ?? null,
            userType: $data['user_type'] ?? null,
            scopes: $data['granted_scopes'] ?? []
        );
    }
}
