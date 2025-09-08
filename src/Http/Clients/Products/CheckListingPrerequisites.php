<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

/**
 * Client para verificar se um produto atende aos pré-requisitos de listagem.
 *
 * @see https://partner.tiktokshop.com/docv2/page/check-listing-prerequisites-202312
 */
class CheckListingPrerequisites
{
    private const ENDPOINT = '/product/202312/prerequisites';

    public function __construct(private HttpClient $http) {}

    /**
     * Executa a verificação dos pré-requisitos para listagem de um produto.
     *
     * @param array<string, mixed> $payload Payload contendo:
     *      - category_id (string|int)
     *      - attributes (array)
     *      - certifications? (array)
     *      - outras chaves exigidas pela categoria.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function check(array $payload): array
    {
        $response = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded = $response->json() ?? [];

        Log::info('[TikTokShop] Check Listing Prerequisites', [
            'payload'  => $payload,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return $this->formatResponse($decoded);
    }

    /**
     * Uniformiza a resposta para o padrão usado por todos os endpoints.
     */
    private function formatResponse(array $decoded): array
    {
        return [
            'success' => ($decoded['code'] ?? -1) === 0,
            'code'    => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data'    => $decoded['data'] ?? [],
        ];
    }
}
