<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para edição parcial de produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/partial-edit-product-202309
 */
class PartialEditProduct
{
    private const ENDPOINT = '/product/202309/products/%s/partial_edit';

    public function __construct(private HttpClient $http) {}

    /**
     * Edita parcialmente um produto existente.
     *
     * @param string $productId ID do produto a ser atualizado
     * @param array<string,mixed> $updates Campos a atualizar (ex.: title, description, price, inventory etc.)
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function update(string $productId, array $updates): array
    {
        $uri = sprintf(self::ENDPOINT, $productId);

        $response = $this->http->postWithAuth($uri, $updates);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Partial Edit Product', [
            'product_id' => $productId,
            'updates'    => $updates,
            'status'     => $response->status(),
            'response'   => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
