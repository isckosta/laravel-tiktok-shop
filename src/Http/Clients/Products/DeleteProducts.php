<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para exclusão de produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/delete-products-202309
 */
class DeleteProducts
{
    private const ENDPOINT = '/product/202309/products';
    private const MAX_IDS  = 20;

    public function __construct(private HttpClient $http) {}

    /**
     * Exclui produtos pelo ID.
     *
     * @param array<int, string> $productIds Lista de IDs dos produtos (máx: 20)
     *
     * @throws \InvalidArgumentException Se a lista for inválida
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function delete(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException('É necessário informar pelo menos 1 product_id.');
        }

        if (count($productIds) > self::MAX_IDS) {
            throw new \InvalidArgumentException(
                sprintf('O máximo permitido é %d product_ids, mas %d foram fornecidos.', self::MAX_IDS, count($productIds))
            );
        }

        $body = ['product_ids' => $productIds];

        $resp = $this->http->deleteWithAuth(self::ENDPOINT, $body);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Delete Products', [
            'body'     => $body,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
