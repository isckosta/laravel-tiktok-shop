<?php

namespace TikTokShop\Http\Clients;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

class OrdersEndpoint
{
    public function __construct(private HttpClient $http) {}

    public function list(array $filters = [], ?string $cursor = null, int $pageSize = 50): array
    {
        $query = [
            'page_size' => $pageSize,
        ];

        if ($cursor) {
            $query['cursor'] = $cursor;
        }

        $body = $filters; // ex.: ['status' => 'CREATED']

        $resp = $this->http->postWithAuth('/order/202309/orders/search', $body, $query)->throw();

        return $resp->json() ?? [];
    }

    public function detail(string $orderId): array
    {
        $body = [
            'order_id_list' => [$orderId], // ou 'order_id' dependendo do uso
        ];

        $query = []; // shop_cipher será injetado pelo HttpClient

        $resp = $this->http->postWithAuth('/order/202309/orders/detail/query', $body, $query)->throw();

        return $resp->json() ?? [];
    }
}
