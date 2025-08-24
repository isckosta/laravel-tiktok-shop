<?php

namespace TikTokShop\Contracts;

use TikTokShop\Http\Clients\ProductsEndpoint;
use TikTokShop\Http\Clients\OrdersEndpoint;

interface TikTokShopClient
{
    public function products(): ProductsEndpoint;
    public function orders(): OrdersEndpoint;
}
