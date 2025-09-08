<?php

namespace TikTokShop\Http\Clients\Seller;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

/**
 * Client para consultar as permissões (scopes) autorizadas para o seller/app.
 *
 * @see https://partner.tiktokshop.com/docv2/page/get-seller-permissions-202309
 */
class GetSellerPermissions
{
    private const ENDPOINT = '/seller/202309/permissions';

    public function __construct(private HttpClient $http) {}

    /**
     * Retorna a lista de permissões que a conta/app possui.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function list(): array
    {
        $response = $this->http->getWithAuth(self::ENDPOINT, [], true);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Get Seller Permissions', [
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return $this->formatResponse($decoded);
    }

    /**
     * Normaliza resposta da API neste formato:
     * success, code, message e data (lista de permissões).
     *
     * @param array<string, mixed> $decoded
     * @return array{success: bool, code: int|null, message: string|null, data: array}
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
