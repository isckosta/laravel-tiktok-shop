<?php

namespace TikTokShop\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokShopCredential extends Model
{
    protected $table = 'tiktok_shop_credentials';

    protected $fillable = [
        'client_hash',
        'shop_cipher',
        'shop_code',
        'shop_id',
        'shop_name',
        'shop_region',
        'shop_seller_type',
        'app_key',
        'app_secret',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'scopes',
    ];

    protected $casts = [
        'scopes' => 'array',
        'access_token_expires_at' => 'datetime',
    ];
}
