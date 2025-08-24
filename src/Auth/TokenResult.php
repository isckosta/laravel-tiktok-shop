<?php

namespace TikTokShop\Auth;

class TokenResult
{
    public string $accessToken;
    public string $refreshToken;
    public \Carbon\Carbon $accessTokenExpiresAt;
    public ?\Carbon\Carbon $refreshTokenExpiresAt;
    public ?string $openId;
    public ?string $sellerName;
    public ?string $sellerRegion;
    public ?int $userType;

    public function __construct(
        string $accessToken,
        string $refreshToken,
        \Carbon\Carbon $accessTokenExpiresAt,
        ?\Carbon\Carbon $refreshTokenExpiresAt = null,
        ?string $openId = null,
        public ?string $shopCipher = null,
        public array $scopes = [],
        ?string $sellerName = null,
        ?string $sellerRegion = null,
        ?int $userType = null,
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
        $this->openId = $openId;
        $this->sellerName = $sellerName;
        $this->sellerRegion = $sellerRegion;
        $this->userType = $userType;
    }
}

