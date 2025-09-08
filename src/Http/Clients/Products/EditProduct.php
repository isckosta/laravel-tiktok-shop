<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para edição completa de produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/edit-product-202309
 */
class EditProduct
{
    private const ENDPOINT = '/product/202309/products/%s';

    public function __construct(private HttpClient $http) {}

    /**
     * Atualiza totalmente um produto existente.
     *
     * @param string $productId ID do produto a ser editado
     * @param array<string, mixed> $payload Dados completos do produto:
     *     - title, description, category_id, main_images, skus, etc.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function update(string $productId, array $payload): array
    {
        $endpoint = sprintf(self::ENDPOINT, $productId);

        $response = $this->http->putWithAuth($endpoint, $payload);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Edit Product', [
            'product_id' => $productId,
            'payload'    => $this->maskSensitiveData($payload),
            'status'     => $response->status(),
            'response'   => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    /**
     * Máscara de dados sensíveis nos logs (ex.: SKUs ou preços).
     */
    private function maskSensitiveData(array $payload): array
    {
        if (isset($payload['skus'])) {
            $payload['skus'] = '[REDACTED]';
        }
        return $payload;
    }
}
