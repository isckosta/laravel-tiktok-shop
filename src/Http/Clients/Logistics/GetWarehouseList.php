<?php

namespace TikTokShop\Http\Clients\Logistics;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

class GetWarehouseList
{
    public function __construct(private HttpClient $http) {}

    /**
     * Lista todos os warehouses disponíveis na conta
     */
    public function list(): array
    {
        $resp = $this->http->getWithAuth('/logistics/202309/warehouses');

        Log::info('[TikTokShop] Resposta bruta de warehouse list', [
            'status' => $resp->status(),
            'body'   => $resp->body(),
        ]);

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao buscar warehouses', [
                'response' => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao buscar lista de warehouses na TikTok Shop.');
        }

        return $resp->json('data') ?? [];
    }
}
