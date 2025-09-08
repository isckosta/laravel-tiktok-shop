<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;

class GetBrands
{
    public function __construct(private HttpClient $http) {}

    /**
     * Lista marcas disponíveis no TikTok Shop.
     *
     * @param int $pageSize Quantidade por página (máx. geralmente 100)
     * @param string|null $cursor Token para próxima página
     */
    public function list(int $pageSize = 50, ?string $cursor = null): array
    {
        $query = [
            'page_size' => $pageSize,
        ];

        if ($cursor) {
            $query['page_token'] = $cursor;
        }

        $resp = $this->http->getWithAuth('/product/202309/brands', $query);

        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Resposta bruta da listagem de marcas', [
            'status'   => $resp->status(),
            'query'    => $query,
            'response' => $decoded,
        ]);

        return [
            'success' => ($decoded['code'] ?? -1) === 0,
            'code'    => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data'    => $decoded['data'] ?? [],
        ];
    }
}
