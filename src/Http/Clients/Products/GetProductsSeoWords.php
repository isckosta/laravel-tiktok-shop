<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Endpoint para buscar sugestões de SEO para produtos.
 *
 * Documentação oficial:
 * @see https://partner.tiktokshop.com/docv2/page/get-products-seo-words-202405
 */
class GetProductsSeoWords
{
    private const ENDPOINT = '/product/202405/products/seo_words';

    public function __construct(private HttpClient $http) {}

    /**
     * Obtém palavras SEO sugeridas para produtos.
     *
     * @param array<string> $productIds Lista de IDs de produtos (máx: 20)
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function get(array $productIds): array
    {
        if (count($productIds) > 20) {
            throw new \InvalidArgumentException('Maximum of 20 product IDs allowed.');
        }

        $query = [
            'product_ids' => $productIds,
        ];

        $resp = $this->http->getWithAuth(self::ENDPOINT, $query);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Get Products SEO Words', [
            'query'    => $query,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
