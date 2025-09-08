<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para ativar (publicar) produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/activate-product-202309
 */
class ActivateProduct
{
    private const ENDPOINT = '/product/202309/products/activate';

    public function __construct(private HttpClient $http) {}

    /**
     * Ativa (publica) produtos existentes.
     *
     * @param array<int, string> $productIds IDs dos produtos (máx. 20)
     * @param array<int, string> $listingPlatforms Plataformas de ativação (default: ['TIKTOK_SHOP'])
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function activate(array $productIds, array $listingPlatforms = ['TIKTOK_SHOP']): array
    {
        $body = [
            'product_ids'       => $productIds,
            'listing_platforms' => $listingPlatforms,
        ];

        $response = $this->http->postWithAuth(self::ENDPOINT, $body);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Activate Products', [
            'body'     => $body,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
