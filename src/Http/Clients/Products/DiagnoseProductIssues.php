<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

class DiagnoseProductIssues
{
    private const ENDPOINT = '/product/202405/products/diagnoses';

    public function __construct(private HttpClient $http) {}

    /**
     * Diagnostica problemas de informações em produtos ativos.
     *
     * @param array<int, string> $productIds Lista de IDs dos produtos (máx: 200)
     */
    public function diagnose(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException('É necessário informar ao menos 1 product_id.');
        }

        if (count($productIds) > 200) {
            throw new \InvalidArgumentException('O máximo permitido é 200 product_ids por requisição.');
        }

        $query = [
            'product_ids' => $productIds,
        ];

        $resp = $this->http->getWithAuth(self::ENDPOINT, $query);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Diagnose Product Issues', [
            'query'    => $query,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
