<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para desativar produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/deactivate-products-202309
 */
class DeactivateProducts
{
    private const ENDPOINT = '/product/202309/products/deactivate';

    public function __construct(private HttpClient $http) {}

    /**
     * Desativa produtos em massa.
     *
     * @param array<int, string> $productIds IDs dos produtos a serem desativados (máx. 20)
     * @param array<int, string> $listingPlatforms Plataformas de listagem (TIKTOK_SHOP ou TOKOPEDIA).
     *                                             Default: ["TIKTOK_SHOP"]
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function deactivate(array $productIds, array $listingPlatforms = ['TIKTOK_SHOP']): array
    {
        $body = [
            'product_ids'      => $productIds,
            'listing_platforms' => $listingPlatforms,
        ];

        $resp = $this->http->postWithAuth(self::ENDPOINT, $body);

        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Deactivate Products', [
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
