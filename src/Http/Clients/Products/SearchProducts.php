<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para buscar produtos com filtros e paginação.
 *
 * @see https://partner.tiktokshop.com/docv2/page/search-products-202502
 */
class SearchProducts
{
    private const ENDPOINT = '/product/202502/products/search';

    public function __construct(private HttpClient $http) {}

    /**
     * Busca produtos com filtros opcionais.
     *
     * @param array<string, mixed> $filters Filtros opcionais (status, seller_skus, create_time_ge, create_time_le, update_time_ge, update_time_le, category_version, listing_quality_tiers, listing_platforms, audit_status, sku_ids, sns_filter, return_draft_version)
     * @param int $pageSize Quantidade de resultados por página (1–100)
     * @param string|null $cursor Token de paginação
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function search(array $filters = [], int $pageSize = 20, ?string $cursor = null): array
    {
        $query = [
            'page_size' => $pageSize,
        ];

        if ($cursor) {
            $query['page_token'] = $cursor;
        }

        $body = array_filter($filters, fn($value) => $value !== null);

        $resp = $this->http->postWithAuth(self::ENDPOINT, $body, $query);

        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Search Products', [
            'query'    => $query,
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
