<?php

namespace TikTokShop\Repositories;

use TikTokShop\Models\TikTokShopCredential;

interface CredentialsRepositoryInterface
{
    public function findByClientHash(string $clientHash): ?TikTokShopCredential;

    public function updateTokens(string $clientHash, array $tokens): void;
}
