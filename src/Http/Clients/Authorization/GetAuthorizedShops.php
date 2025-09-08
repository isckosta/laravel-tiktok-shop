<?php

namespace TikTokShop\Http\Clients\Authorization;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para buscar as lojas autorizadas da conta/app.
 *
 * @see https://partner.tiktokshop.com/docv2/page/get-authorized-shops-202309
 */
class GetAuthorizedShops
{
    private const ENDPOINT   = '/authorization/202309/shops';
    private const PAGE_SIZE_DEFAULT = 50;
    private const PAGE_SIZE_MAX     = 100;

    public function __construct(private HttpClient $http) {}

    /**
     * Lista as lojas autorizadas para a conta/app autenticada.
     *
     * @param int $pageSize Quantidade de registros por página (1–100).
     *                      O valor padrão é 50 e o máximo permitido é 100.
     * @param string|null $cursor Token de paginação para buscar a próxima página.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function list(int $pageSize = self::PAGE_SIZE_DEFAULT, ?string $cursor = null): array
    {
        $query = $this->buildQuery($pageSize, $cursor);

        $response = $this->http->getWithAuth(self::ENDPOINT, $query, true);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Get Authorized Shops', [
            'query'    => $query,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    /**
     * Monta a query de paginação validando limites de pageSize.
     *
     * @return array<string, int|string>
     */
    private function buildQuery(int $pageSize, ?string $cursor): array
    {
        $pageSize = min($pageSize, self::PAGE_SIZE_MAX);

        $query = ['page_size' => $pageSize];

        if ($cursor !== null) {
            $query['page_token'] = $cursor;
        }

        return $query;
    }
}
