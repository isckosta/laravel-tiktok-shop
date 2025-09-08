<?php

namespace TikTokShop\Http\Clients\Webhooks;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

/**
 * Client para atualizar um webhook configurado na loja.
 *
 * @see https://partner.tiktokshop.com/docv2/page/update-shop-webhook-202309
 */
class UpdateShopWebhook
{
    private const ENDPOINT = '/event/202309/webhooks/update';

    public function __construct(private HttpClient $http) {}

    /**
     * Atualiza o webhook com novos dados.
     *
     * @param string $webhookId Identificador único do webhook configurado.
     * @param string $url URL callback onde receberá os eventos.
     * @param array<int, string> $eventTypes Lista de eventos que o webhook deve ouvir.
     * @param bool $enabled Indica se o webhook está ativo ou não.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function update(
        string $webhookId,
        string $url,
        array $eventTypes,
        bool $enabled = true
    ): array {
        $payload = [
            'webhook_id'  => $webhookId,
            'url'         => $url,
            'event_types' => $eventTypes,
            'status'      => $enabled ? 'ENABLE' : 'DISABLE',
        ];

        $response = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Update Shop Webhook', [
            'payload'  => $payload,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return $this->formatResponse($decoded);
    }

    /**
     * Padroniza resposta da API.
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
