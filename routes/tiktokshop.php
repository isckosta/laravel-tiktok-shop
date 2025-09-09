<?php

use Illuminate\Support\Facades\Route;
use TikTokShop\Http\Controllers\Stubs\TikTokShopAuthController;
use TikTokShop\Webhooks\WebhookController;

Route::get('/tiktok/authorize', [TikTokShopAuthController::class, 'redirect'])->name('tiktokshop.authorize');
Route::get('/tiktok/callback', [TikTokShopAuthController::class, 'callback'])->name('tiktokshop.callback');
Route::post(config('tiktokshop.webhooks.route'), [WebhookController::class, 'handle'])->name('tiktokshop.webhook');

