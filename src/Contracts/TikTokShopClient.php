<?php

namespace TikTokShop\Contracts;

use TikTokShop\Http\Clients\Orders\Orders;
use TikTokShop\Http\Clients\Products\Products;

interface TikTokShopClient
{
    public function products(): Products;

    public function orders(): Orders;
}
