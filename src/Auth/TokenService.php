<?php

namespace TikTokShop\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TikTokShop\Models\TikTokShopCredential;
use TikTokShop\Repositories\CredentialsRepositoryInterface;
use TikTokShop\Support\Signer;

class TokenService
{
    public function __construct(private CredentialsRepositoryInterface $repo) {}

    /**
     * Troca auth_code por tokens e obtém shop_cipher.
     */
    public function exchangeAuthorizedCode(string $authCode): TokenResult
    {
        try {

            $resp = Http::get('https://auth.tiktok-shops.com/api/v2/token/get', [
                'grant_type' => 'authorized_code',
                'auth_code'  => $authCode,
                'app_key'    => config('tiktokshop.auth.app_key'),
                'app_secret' => config('tiktokshop.auth.app_secret'),
            ])->json();

            if (! isset($resp['data']['access_token'])) {
                $code = $resp['code'] ?? null;

                if ((int) $code === 36004004) {
                    throw new \DomainException('A loja já foi autorizada anteriormente.');
                }

                throw new \RuntimeException('Falha ao obter access_token: ' . json_encode($resp));
            }

            $data = $resp['data'];
            $accessToken = $data['access_token'];

            $authorizedShops = $this->fetchAuthorizedShopsFromApi($accessToken);

            $shopCipher      = $authorizedShops[0]['cipher']      ?? null;
            $shopCode        = $authorizedShops[0]['code']        ?? null;
            $shopId          = $authorizedShops[0]['id']          ?? null;
            $shopName        = $authorizedShops[0]['name']        ?? null;
            $shopRegion      = $authorizedShops[0]['region']      ?? null;
            $shopSellerType  = $authorizedShops[0]['seller_type'] ?? null;

            return $this->mapTokenResult(array_merge($data, [
                'shop_cipher' => $shopCipher,
                'shop_code'   => $shopCode,
                'shop_id'     => $shopId,
                'shop_name'   => $shopName,
                'shop_region' => $shopRegion,
                'shop_seller_type' => $shopSellerType,
            ]));
        } catch (\Exception $e) {
            Log::error('Erro ao obter token do TikTok Shop', ['error' => $e->getMessage()]);
            throw $e;
        }
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
            'shop_code'               => $token->shopCode,
            'shop_id'                 => $token->shopId,
            'shop_name'               => $token->shopName,
            'shop_region'             => $token->shopRegion,
            'shop_seller_type'        => $token->shopSellerType,
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

        Log::info('Resposta da API de autorização TikTok Shop: Shops', ['response' => $response->json()]);

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

        $accessExpires = isset($data['access_token_expire_in'])
            ? \Carbon\Carbon::createFromTimestamp((int)$data['access_token_expire_in'])
            : now()->addSeconds(7200);

        $refreshExpires = isset($data['refresh_token_expire_in'])
            ? \Carbon\Carbon::createFromTimestamp((int)$data['refresh_token_expire_in'])
            : null;

        return new TokenResult(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            accessTokenExpiresAt: $accessExpires,
            refreshTokenExpiresAt: $refreshExpires,
            openId: $data['open_id'] ?? null,
            shopCipher: $data['shop_cipher'] ?? null,
            shopCode: $data['shop_code'] ?? null,
            shopId: $data['shop_id'] ?? null,
            shopName: $data['shop_name'] ?? null,
            shopRegion: $data['shop_region'] ?? null,
            shopSellerType: $data['shop_seller_type'] ?? null,
            sellerName: $data['seller_name'] ?? null,
            sellerRegion: $data['seller_base_region'] ?? null,
            userType: $data['user_type'] ?? null,
            scopes: $data['granted_scopes'] ?? []
        );
    }
}
