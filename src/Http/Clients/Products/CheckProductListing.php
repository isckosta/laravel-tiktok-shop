<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use TikTokShop\Traits\FormatsApiResponse;

/**
 * Client para checar se um produto pode ser listado na TikTok Shop.
 *
 * @see https://partner.tiktokshop.com/docv2/page/check-product-listing-202309
 */
class CheckProductListing
{
    private const ENDPOINT = '/product/202309/products/listing_check';

    public function __construct(private HttpClient $http) {}

    /**
     * Valida os dados de um produto antes de criar/listar.
     *
     * @param array<string, mixed> $payload Estrutura de dados do produto, incluindo:
     *  - description (string, HTML, obrigatório)
     *  - category_id (string, obrigatório)
     *  - brand_id (string, obrigatório)
     *  - main_images (array<object>, obrigatório)
     *      - uri (string)
     *  - skus (array<object>, obrigatório)
     *      - sales_attributes (array<object>)
     *          - id (string)
     *          - name (string)
     *          - value_id (string)
     *          - value_name (string)
     *          - sku_img (object { uri: string })
     *          - supplementary_sku_images (array<object { uri: string }>)
     *      - seller_sku (string)
     *      - price (object { amount: string, currency: string, sale_price?: string })
     *      - external_sku_id (string)
     *      - identifier_code (object { code: string, type: string })
     *      - inventory (array<object { warehouse_id: string, quantity: int }>)
     *      - combined_skus (array<object { product_id: string, sku_id: string, sku_count: int, sku_unit_count?: string }>)
     *      - external_urls (array<string>)
     *      - extra_identifier_codes (array<string>)
     *      - pre_sale (object {
     *          type: string,
     *          fulfillment_type: object { handling_duration_days?: int, release_date?: int }
     *      })
     *      - list_price (object { amount: string, currency: string })
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function check(array $payload): array
    {
        $response = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Check Product Listing', [
            'payload'  => $payload,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
