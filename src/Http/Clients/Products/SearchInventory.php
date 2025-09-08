<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para consulta de inventário de produtos na TikTok Shop.
 *
 * @see https://partner.tiktokshop.com/docv2/page/inventory-search-202309
 */
class SearchInventory
{
    private const ENDPOINT = '/product/202309/inventory/search';

    public function __construct(private HttpClient $http) {}

    /**
     * Busca inventário por product_ids ou sku_ids.
     *
     * @param array<int, string> $productIds Lista de IDs de produtos (máx. 100)
     * @param array<int, string> $skuIds Lista de IDs de SKUs (máx. 600)
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function search(array $productIds = [], array $skuIds = []): array
    {
        if (empty($productIds) && empty($skuIds)) {
            throw new \InvalidArgumentException(
                'É necessário informar pelo menos productIds ou skuIds para consultar inventário.'
            );
        }

        if (count($productIds) > 100) {
            throw new \InvalidArgumentException('O número máximo de productIds é 100.');
        }

        if (count($skuIds) > 600) {
            throw new \InvalidArgumentException('O número máximo de skuIds é 600.');
        }

        $body = [];
        if (!empty($skuIds)) {
            $body['sku_ids'] = $skuIds;
        } else {
            $body['product_ids'] = $productIds;
        }

        $resp = $this->http->postWithAuth(self::ENDPOINT, $body);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Search Inventory', [
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
