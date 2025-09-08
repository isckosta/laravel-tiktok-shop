<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para buscar size charts autorizados.
 *
 * @see https://partner.tiktokshop.com/docv2/page/search-size-charts-202407
 */
class SearchSizeCharts
{
    private const ENDPOINT = '/product/202407/sizecharts/search';

    public function __construct(private HttpClient $http) {}

    /**
     * Busca templates de size charts por página, IDs ou palavra-chave.
     *
     * @param int $pageSize Quantidade de registros por página (1–100)
     * @param string|null $cursor Token de paginação
     * @param array<int, string> $ids IDs dos size charts (prioridade sobre keyword)
     * @param string|null $keyword Palavra-chave para busca por nome
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function search(int $pageSize = 20, ?string $cursor = null, array $ids = [], ?string $keyword = null): array {

        $query = [
            'page_size' => $pageSize,
        ];

        if ($cursor) {
            $query['page_token'] = $cursor;
        }

        $body = [];
        if (!empty($ids)) {
            $body['ids'] = $ids;
        } elseif ($keyword) {
            $body['keyword'] = $keyword;
        }

        $resp = $this->http->postWithAuth(self::ENDPOINT, $body, $query, true);

        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Search Size Charts', [
            'query'    => $query,
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}

