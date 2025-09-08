<?php

namespace TikTokShop;

use RuntimeException;
use TikTokShop\Auth\TokenService;
use TikTokShop\Contracts\TikTokShopClient;
use TikTokShop\Http\Clients\Authorization\GetAuthorizedShops;
use TikTokShop\Http\Clients\Event\GetShopWebhooks;
use TikTokShop\Http\Clients\Logistics\GetWarehouseList;
use TikTokShop\Http\Clients\Orders\Orders;
use TikTokShop\Http\Clients\Products\CheckProductListing;
use TikTokShop\Http\Clients\Products\GetBrands;
use TikTokShop\Http\Clients\Products\GetCategories;
use TikTokShop\Http\Clients\Products\CheckListingPrerequisites;
use TikTokShop\Http\Clients\Products\GetAttributes;
use TikTokShop\Http\Clients\Products\GetCategoryRules;
use TikTokShop\Http\Clients\Products\Products;
use TikTokShop\Http\Clients\Products\RecommendCategory;
use TikTokShop\Http\Clients\Products\SearchSizeCharts;
use TikTokShop\Http\Clients\Products\UploadProductFile;
use TikTokShop\Http\Clients\Seller\GetSellerPermissions;
use TikTokShop\Http\HttpClient;
use TikTokShop\Repositories\CredentialsRepositoryInterface;

/**
 * Manager central para criar conexões com a API da TikTok Shop.
 *
 * Responsável por instanciar o HttpClient já autenticado e expor
 * os endpoints disponíveis via contratos de client.
 */
class TikTokShopManager
{
    private const DEFAULT_CONNECTION = 'default';

    public function __construct(
        private array $config,
        private CredentialsRepositoryInterface $credentialsRepo,
        private TokenService $tokens
    ) {}

    /**
     * Retorna um client autenticado para a conexão solicitada.
     *
     * @param string|null $clientHash Identificador único da conexão (ou null para usar a padrão).
     * @throws RuntimeException Caso as credenciais não sejam encontradas.
     */
    public function connection(?string $clientHash = null): TikTokShopClient
    {
        $clientHash ??= $this->config['default_connection'] ?? self::DEFAULT_CONNECTION;

        $credentials = $this->credentialsRepo->findByClientHash($clientHash);
        if (! $credentials) {
            throw new RuntimeException("Credentials not found for client_hash={$clientHash}");
        }

        $http = $this->makeHttpClient($credentials->app_key, $credentials->app_secret, $credentials->access_token, $clientHash);

        return new class($http) implements TikTokShopClient {
            public function __construct(private HttpClient $http) {}

            public function products(): Products
            {
                return new Products($this->http);
            }

            public function orders(): Orders
            {
                return new Orders($this->http);
            }

            public function categories(): GetCategories
            {
                return new GetCategories($this->http);
            }

            public function brands(): GetBrands
            {
                return new GetBrands($this->http);
            }

            public function attributes(): GetAttributes
            {
                return new GetAttributes($this->http);
            }

            public function warehouses(): GetWarehouseList
            {
                return new GetWarehouseList($this->http);
            }

            public function categoryRules(): GetCategoryRules
            {
                return new GetCategoryRules($this->http);
            }

            public function authorizedShops(): GetAuthorizedShops
            {
                return new GetAuthorizedShops($this->http);
            }

            public function files(): UploadProductFile
            {
                return new UploadProductFile($this->http);
            }

            public function webhooks(): GetShopWebhooks
            {
                return new GetShopWebhooks($this->http);
            }

            public function updateWebhook(): UpdateShopWebhook
            {
                return new UpdateShopWebhook($this->http);
            }

            public function deleteWebhook(): DeleteShopWebhook
            {
                return new DeleteShopWebhook($this->http);
            }

            public function permissions(): GetSellerPermissions
            {
                return new GetSellerPermissions($this->http);
            }

            public function prerequisites(): CheckListingPrerequisites
            {
                return new CheckListingPrerequisites($this->http);
            }

            public function recommendCategory(): RecommendCategory
            {
                return new RecommendCategory($this->http);
            }

            public function productListing(): CheckProductListing
            {
                return new CheckProductListing($this->http);
            }

            public function sizeCharts(): SearchSizeCharts
            {
                return new SearchSizeCharts($this->http);
            }
        };
    }

    /**
     * Cria um HttpClient autenticado e pronto para uso.
     */
    private function makeHttpClient(string $appKey, string $appSecret, ?string $accessToken, string $clientHash): HttpClient
    {
        return new HttpClient(
            baseUri: $this->config['http']['base_uri'] ?? '',
            timeout: (int)($this->config['http']['timeout'] ?? 30),
            appKey: $appKey,
            appSecret: $appSecret,
            accessToken: $accessToken,
            factory: null,
            credsRepo: $this->credentialsRepo,
            tokens: $this->tokens,
            clientHash: $clientHash
        );
    }
}
