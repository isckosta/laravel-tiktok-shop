<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para atualização de inventário de produtos na TikTok Shop.
 *
 * @see https://partner.tiktokshop.com/docv2/page/update-inventory-202309
 */
class UpdateProductInventory
{
    private const ENDPOINT = '/product/202309/products/%s/inventory/update';

    public function __construct(private HttpClient $http) {}

    /**
     * Atualiza inventário de SKUs de um produto.
     *
     * @param string $productId ID do produto
     * @param array<int, array{
     *     id: string,
     *     inventory: array<int, array{
     *         warehouse_id?: string,
     *         quantity: int
     *     }>
     * }> $skus
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function update(string $productId, array $skus): array
    {
        if (empty($skus)) {
            throw new \InvalidArgumentException('É necessário informar pelo menos 1 SKU para atualização de inventário.');
        }

        $body = ['skus' => $skus];
        $endpoint = sprintf(self::ENDPOINT, $productId);

        $resp = $this->http->postWithAuth($endpoint, $body);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Update Product Inventory', [
            'endpoint' => $endpoint,
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
