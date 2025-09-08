<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;

class GetCategoryRules
{
    public function __construct(private HttpClient $http)
    {
    }

    /**
     * Busca regras específicas da categoria (como atributos adicionais, exigências regionais, certificados, etc.).
     *
     * @param int|string $categoryId ID da categoria a consultar
     * @return array Estrutura com success, code, message e data (regras)
     */
    public function list(int|string $categoryId): array
    {
        $resp = $this->http->getWithAuth("/product/202309/categories/{$categoryId}/rules");

        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Resposta bruta de category rules', [
            'status' => $resp->status(),
            'category_id' => $categoryId,
            'response' => $decoded,
        ]);

        return [
            'success' => ($decoded['code'] ?? -1) === 0,
            'code' => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data' => $decoded['data'] ?? [],
        ];
    }
}
