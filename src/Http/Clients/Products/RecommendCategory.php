<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para recomendar categorias de produto a partir de título e descrição.
 *
 * @see https://partner.tiktokshop.com/docv2/page/recommend-category-202309
 */
class RecommendCategory
{
    private const ENDPOINT = '/product/202309/categories/recommend';

    public function __construct(private HttpClient $http) {}

    /**
     * Recomenda categorias para um produto com base no título e descrição.
     *
     * @param string $title       Título do produto.
     * @param string $description Descrição detalhada do produto.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function recommend(string $title, string $description): array
    {
        $body = [
            'product_title'       => $title,
            'product_description' => $description,
        ];

        $response = $this->http->postWithAuth(self::ENDPOINT, $body);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Recommend Category', [
            'title'       => $title,
            'description' => $description,
            'status'      => $response->status(),
            'response'    => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
