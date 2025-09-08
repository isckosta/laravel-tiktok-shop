<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para atualização de preços de produtos na TikTok Shop.
 *
 * @see https://partner.tiktokshop.com/docv2/page/update-price-202309
 */
class UpdateProductPrices
{
    private const ENDPOINT = '/product/202309/products/%s/prices/update';

    public function __construct(private HttpClient $http) {}

    /**
     * Atualiza preços de SKUs de um produto.
     *
     * @param string $productId ID do produto
     * @param array<int, array{
     *     id: string,
     *     price: array{amount: string, currency: string, sale_price?: string},
     *     list_price?: array{amount: string, currency: string},
     *     external_list_prices?: array<int, array{
     *         source: string,
     *         amount: string,
     *         currency: string
     *     }>
     * }> $skus
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function update(string $productId, array $skus): array
    {
        if (empty($skus)) {
            throw new \InvalidArgumentException('É necessário informar pelo menos 1 SKU para atualização de preços.');
        }

        $body = ['skus' => $skus];
        $endpoint = sprintf(self::ENDPOINT, $productId);

        $resp = $this->http->postWithAuth($endpoint, $body);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Update Product Prices', [
            'endpoint' => $endpoint,
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
