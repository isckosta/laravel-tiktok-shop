<?php

namespace TikTokShop;

use Illuminate\Support\ServiceProvider;
use TikTokShop\Console\SyncProductsCommand;
use TikTokShop\Repositories\CredentialsRepositoryInterface;
use TikTokShop\Repositories\EloquentCredentialsRepository;
use TikTokShop\Auth\TokenService;
use TikTokShop\Console\AuthorizeCommand;

class TikTokShopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tiktokshop.php', 'tiktokshop');

        $this->app->singleton(CredentialsRepositoryInterface::class, function () {
            return new EloquentCredentialsRepository();
        });

        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService(
                $app->make(CredentialsRepositoryInterface::class)
            );
        });

        $this->app->singleton('tiktokshop.manager', function ($app) {
            return new TikTokShopManager(
                config('tiktokshop'),
                $app->make(CredentialsRepositoryInterface::class),
                $app->make(TokenService::class)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/tiktokshop.php' => config_path('tiktokshop.php'),
        ], 'tiktokshop-config');

        if (! class_exists('CreateTikTokShopCredentialsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/2025_08_15_000000_create_tiktok_shop_credentials_table.php'
                => database_path('migrations/2025_08_15_000000_create_tiktok_shop_credentials_table.php'),
            ], 'tiktokshop-migrations');
        }

        if (! class_exists('CreateTikTokShopProductsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/2025_08_23_000000_create_tiktok_shop_products_table.php'
                => database_path('migrations/2025_08_23_000000_create_tiktok_shop_products_table.php'),
            ], 'tiktokshop-migrations');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/tiktokshop.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tiktokshop');

        if ($this->app->runningInConsole()) {
            $this->commands([
                AuthorizeCommand::class,
                SyncProductsCommand::class,
            ]);
        }
    }
}
