<?php

namespace TikTokShop\Models;

use Illuminate\Database\Eloquent\Model;

class TikTokShopProduct extends Model
{
    protected $table = 'tiktok_shop_products';

    protected $fillable = [
        'tiktok_id',
        'name',
        'status',
        'price',
        'currency',
        'shop_cipher',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'price' => 'decimal:2',
    ];
}
