<?php

namespace TikTokShop\Repositories;

use TikTokShop\Models\TikTokShopCredential;

class EloquentCredentialsRepository implements CredentialsRepositoryInterface
{
    public function findByClientHash(string $clientHash): ?TikTokShopCredential
    {
        return TikTokShopCredential::where('client_hash', $clientHash)->first();
    }

    public function updateTokens(string $clientHash, array $tokens): void
    {
        $cred = $this->findByClientHash($clientHash);
        if (! $cred) {
            return;
        }
        $cred->fill([
            'access_token'  => $tokens['access_token'] ?? $cred->access_token,
            'refresh_token' => $tokens['refresh_token'] ?? $cred->refresh_token,
            'access_token_expires_at' => $tokens['access_token_expires_at'] ?? $cred->access_token_expires_at,
        ])->save();
    }
}
