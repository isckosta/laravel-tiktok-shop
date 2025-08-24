<?php

namespace TikTokShop\Facades;

use Illuminate\Support\Facades\Facade;

class TikTokShop extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tiktokshop.manager';
    }
}
