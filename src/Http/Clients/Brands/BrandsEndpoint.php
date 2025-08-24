<?php

namespace TikTokShop\Http\Clients\Brands;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

class BrandsEndpoint
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

        $resp = $this->http->getWithAuth('/product/202309/brands', $query)->throw();

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao buscar marcas', [
                'query'     => $query,
                'response'  => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao buscar marcas da TikTok Shop.');
        }

        return $resp->json('data') ?? [];
    }
}
