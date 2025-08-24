<?php

use Illuminate\Support\Facades\Route;
use TikTokShop\Webhooks\WebhookController;
use TikTokShop\Auth\AuthorizationController;

Route::get('/tiktok/authorize', [AuthorizationController::class, 'redirect'])->name('tiktokshop.authorize');
Route::get('/tiktok/callback', [AuthorizationController::class, 'callback'])->name('tiktokshop.callback');
Route::post(config('tiktokshop.webhooks.route'), [WebhookController::class, 'handle'])->name('tiktokshop.webhook');

