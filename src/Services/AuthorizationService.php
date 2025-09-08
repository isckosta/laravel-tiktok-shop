<?php

namespace TikTokShop\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use TikTokShop\Auth\TokenService;
use TikTokShop\Repositories\CredentialsRepositoryInterface;

class AuthorizationService
{
    public function __construct(private TokenService $tokens) {}

    /**
     * Gera a URL de autorização do TikTok Shop.
     * @param string $clientHash
     * @return string
     */
    public function generateAuthorizationUrl(string $clientHash = 'default'): string
    {
        $state = Str::uuid()->toString();

        Cache::put("ttshop:oauth:state:{$state}", [
            'client_hash' => $clientHash,
        ], now()->addMinutes(10));

        $query = http_build_query([
            'app_key'       => config('tiktokshop.auth.app_key'),
            'state'         => $state,
            'redirect_uri'  => config('tiktokshop.auth.redirect_uri'),
            'response_type' => 'code',
        ]);

        return rtrim(config('tiktokshop.auth.base_uri'), '/') . "/oauth/authorize?" . $query;
    }

    /**
     * Processa o callback, troca o auth_code por token e salva.
     * @param string $authCode
     * @param string|null $state
     * @return array
     */
    public function handleCallback(string $authCode, ?string $state = null): array
    {
        $clientHash = $state
            ? optional(Cache::pull("ttshop:oauth:state:{$state}"))['client_hash'] ?? 'default'
            : 'default';

        try {
            $token = $this->tokens->exchangeAuthorizedCode($authCode);

            $this->tokens->storeOrUpdate($clientHash, $token);

            return [
                'status'       => 'authorized',
                'client_hash'  => $clientHash,
                'open_id'      => $token->openId,
                'expires_at'   => $token->accessTokenExpiresAt->toIso8601String(),
                'shop_name'    => $token->shopName,
                'shop_id'      => $token->shopId,
                'shop_region'  => $token->shopRegion,
                'seller_type'  => $token->shopSellerType,
            ];
        } catch (\DomainException $e) {
            // Loja já autorizada anteriormente → buscar no repositório
            $credentials = app(CredentialsRepositoryInterface::class)->findByClientHash($clientHash);

            return [
                'status'      => 'already_authorized',
                'client_hash' => $clientHash,
                'message'     => $e->getMessage(),
                'shop_name'   => $credentials->shop_name        ?? null,
                'shop_id'     => $credentials->shop_id          ?? null,
                'shop_region' => $credentials->shop_region      ?? null,
                'seller_type' => $credentials->shop_seller_type ?? null,
            ];
        }
    }
}
