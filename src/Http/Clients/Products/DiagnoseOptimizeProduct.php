<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para diagnosticar e otimizar produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/diagnose-and-optimize-product-202411
 */
class DiagnoseOptimizeProduct
{
    private const ENDPOINT = '/product/202411/products/diagnose_optimize';

    public function __construct(private HttpClient $http) {}

    /**
     * Diagnostica e sugere otimizações para um produto.
     *
     * @param array<string, mixed> $payload Dados do produto para diagnóstico e otimização.
     *   - product_id?: string
     *   - category_id: string
     *   - description?: string
     *   - brand_id?: string
     *   - main_images?: array<array{uri: string, title?: string}>
     *   - product_attributes?: array<array{id: string, values: array<array{id?: string, name?: string}>>>
     *   - size_chart?: array{template?: array{id?: string}, image?: array{uri?: string}}
     *   - optimization_fields?: array<string> ["TITLE", "DESCRIPTION", "IMAGE", "ALL", "NONE"]
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function diagnose(array $payload): array
    {
        $resp = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Diagnose & Optimize Product', [
            'payload'  => $payload,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
