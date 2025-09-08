<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para criar marcas personalizadas na TikTok Shop.
 *
 * Permite adicionar novas marcas que ainda não existam na lista oficial.
 *
 * @see https://partner.tiktokshop.com/docv2/page/create-custom-brands-202309
 */
class CreateCustomBrand
{
    private const ENDPOINT = '/product/202309/brands';

    public function __construct(private HttpClient $http) {}

    /**
     * Cria uma marca personalizada.
     *
     * @param string $brandName Nome da nova marca
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function create(string $brandName): array
    {
        $payload = ['brand_name' => $brandName];

        $response = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Create Custom Brand', [
            'brand_name' => $brandName,
            'status'     => $response->status(),
            'response'   => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
