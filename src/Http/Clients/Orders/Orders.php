<?php

namespace TikTokShop\Http\Clients\Orders;

use TikTokShop\Http\HttpClient;

class Orders
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

        $body = $filters;

        $resp = $this->http->postWithAuth('/order/202309/orders/search', $body, $query)->throw();

        return $resp->json() ?? [];
    }

    public function detail(string $orderId): array
    {
        $body = [
            'order_id_list' => [$orderId],
        ];

        $query = [];

        $resp = $this->http->postWithAuth('/order/202309/orders/detail/query', $body, $query)->throw();

        return $resp->json() ?? [];
    }
}
