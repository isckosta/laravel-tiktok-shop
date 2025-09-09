<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

class ProductSuggestions
{
    private const ENDPOINT = '/product/202405/products/suggestions';

    public function __construct(private HttpClient $http) {}

    /**
     * Obtém sugestões de título e descrição para produtos ativos.
     *
     * @param array<string> $productIds IDs dos produtos (máx. 20, precisam estar em status ACTIVATE)
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function get(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException('At least one product_id is required.');
        }

        if (count($productIds) > 20) {
            throw new \InvalidArgumentException('Maximum of 20 product_ids is allowed.');
        }

        $query = ['product_ids' => $productIds];

        $resp = $this->http->getWithAuth(self::ENDPOINT, $query);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Get Product Suggestions', [
            'endpoint' => self::ENDPOINT,
            'query'    => $query,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
