<?php

namespace TikTokShop;

use TikTokShop\Contracts\TikTokShopClient;
use TikTokShop\Http\Clients\Brands\BrandsEndpoint;
use TikTokShop\Http\Clients\Categories\CategoriesEndpoint;
use TikTokShop\Http\HttpClient;
use TikTokShop\Repositories\CredentialsRepositoryInterface;
use TikTokShop\Http\Clients\ProductsEndpoint;
use TikTokShop\Http\Clients\OrdersEndpoint;
use TikTokShop\Auth\TokenService;

class TikTokShopManager
{
    public function __construct(
        private array $config,
        private CredentialsRepositoryInterface $credentialsRepo,
        private TokenService $tokens
    ) {}

    public function connection(?string $clientHash = null): TikTokShopClient
    {
        $clientHash ??= $this->config['default_connection'] ?? 'default';

        $credentials = $this->credentialsRepo->findByClientHash($clientHash);
        if (! $credentials) {
            throw new \RuntimeException("Credentials not found for client_hash={$clientHash}");
        }

        $http = new HttpClient(
            baseUri: $this->config['http']['base_uri'] ?? '',
            timeout: (int)($this->config['http']['timeout'] ?? 30),
            appKey: $credentials->app_key,
            appSecret: $credentials->app_secret,
            accessToken: $credentials->access_token,
            factory: null,
            credsRepo: $this->credentialsRepo,
            tokens: $this->tokens,
            clientHash: $clientHash
        );

        return new class($http) implements TikTokShopClient {
            public function __construct(private HttpClient $http) {}

            public function products(): ProductsEndpoint
            {
                return new ProductsEndpoint($this->http);
            }

            public function orders(): OrdersEndpoint
            {
                return new OrdersEndpoint($this->http);
            }

            public function categories(): CategoriesEndpoint
            {
                return new CategoriesEndpoint($this->http);
            }

            public function brands(): BrandsEndpoint
            {
                return new BrandsEndpoint($this->http);
            }
        };
    }
}
