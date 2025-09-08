<?php

namespace TikTokShop\Http\Clients\Event;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para listar os webhooks configurados na loja.
 *
 * @see https://partner.tiktokshop.com/docv2/page/get-shop-webhooks-202309
 */
class GetShopWebhooks
{
    private const ENDPOINT         = '/event/202309/webhooks';
    private const PAGE_SIZE_DEFAULT = 50;
    private const PAGE_SIZE_MAX     = 100;

    public function __construct(private HttpClient $http) {}

    /**
     * Retorna os webhooks configurados para a loja.
     *
     * @param int $pageSize Quantidade de registros por página (1–100).
     * @param string|null $cursor Token para próxima página.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function list(int $pageSize = self::PAGE_SIZE_DEFAULT, ?string $cursor = null): array
    {
        $query = $this->prepareQuery($pageSize, $cursor);

        $response = $this->http->getWithAuth(self::ENDPOINT, $query);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Get Shop Webhooks', [
            'query'    => $query,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    /**
     * Prepara e normaliza os parâmetros de query.
     *
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
}
