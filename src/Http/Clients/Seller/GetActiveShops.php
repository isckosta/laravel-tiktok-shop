<?php

namespace TikTokShop\Http\Clients\Authorization;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

/**
 * Client para listar as lojas ativas vinculadas à conta/app.
 *
 * @see https://partner.tiktokshop.com/docv2/page/get-active-shops-202309
 */
class GetActiveShops
{
    private const ENDPOINT = '/authorization/202309/active_shops';
    private const PAGE_SIZE_DEFAULT = 50;
    private const PAGE_SIZE_MAX     = 100;

    public function __construct(private HttpClient $http) {}

    /**
     * Lista as lojas ativas da conta.
     *
     * @param int $pageSize Quantidade de itens por página (1–100).
     * @param string|null $cursor Token para paginação.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function list(int $pageSize = self::PAGE_SIZE_DEFAULT, ?string $cursor = null): array
    {
        $query = $this->prepareQuery($pageSize, $cursor);

        $response = $this->http->getWithAuth(self::ENDPOINT, $query, true);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Get Active Shops', [
            'query'    => $query,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return $this->formatResponse($decoded);
    }

    /**
     * Monta a query de paginação e garante limites.
     *
     * @param int $pageSize
     * @param string|null $cursor
     * @return array<string, mixed>
     */
    private function prepareQuery(int $pageSize, ?string $cursor): array
    {
        $pageSize = min($pageSize, self::PAGE_SIZE_MAX);
        $query = ['page_size' => $pageSize];

        if ($cursor !== null) {
            $query['page_token'] = $cursor;
        }

        return $query;
    }

    /**
     * Normaliza a resposta da API para um formato uniforme.
     *
     * @param array<string, mixed> $decoded
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    private function formatResponse(array $decoded): array
    {
        return [
            'success' => ($decoded['code'] ?? -1) === 0,
            'code'    => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data'    => $decoded['data'] ?? [],
        ];
    }
}
