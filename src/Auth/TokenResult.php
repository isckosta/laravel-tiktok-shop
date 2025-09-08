<?php

namespace TikTokShop\Auth;

use Carbon\Carbon;

class TokenResult
{
    public string $accessToken;
    public string $refreshToken;
    public Carbon $accessTokenExpiresAt;
    public ?Carbon $refreshTokenExpiresAt;
    public ?string $openId;
    public array $scopes;
    public ?string $shopCipher;
    public ?string $shopCode;
    public ?string $shopId;
    public ?string $shopName;
    public ?string $shopRegion;
    public ?string $shopSellerType;
    public ?string $sellerName;
    public ?string $sellerRegion;
    public ?int $userType;

    public function __construct(
        string  $accessToken,
        string  $refreshToken,
        Carbon  $accessTokenExpiresAt,
        ?Carbon $refreshTokenExpiresAt = null,
        ?string $openId = null,
        array   $scopes = [],
        ?string $shopCipher = null,
        ?string $shopCode = null,
        ?string $shopId = null,
        ?string $shopName = null,
        ?string $shopRegion = null,
        ?string $shopSellerType = null,
        ?string $sellerName = null,
        ?string $sellerRegion = null,
        ?int    $userType = null,
    ) {
        $this->accessToken           = $accessToken;
        $this->refreshToken          = $refreshToken;
        $this->accessTokenExpiresAt  = $accessTokenExpiresAt;
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
        $this->openId                = $openId;
        $this->scopes                = $scopes;
        $this->shopCipher            = $shopCipher;
        $this->shopCode              = $shopCode;
        $this->shopId                = $shopId;
        $this->shopName              = $shopName;
        $this->shopRegion            = $shopRegion;
        $this->shopSellerType        = $shopSellerType;
        $this->sellerName            = $sellerName;
        $this->sellerRegion          = $sellerRegion;
        $this->userType              = $userType;
    }
}


