<?php

namespace TikTokShop\Http\Clients\Webhooks;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para deletar um webhook de loja registrado.
 *
 * @see https://partner.tiktokshop.com/docv2/page/delete-shop-webhook-202309
 */
class DeleteShopWebhook
{
    private const ENDPOINT = '/event/202309/webhooks/delete';

    public function __construct(private HttpClient $http) {}

    /**
     * Deleta o webhook configurado para a loja.
     *
     * @param string $webhookId Identificador único do webhook a remover.
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function delete(string $webhookId): array
    {
        $payload = ['webhook_id' => $webhookId];

        $response = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Delete Shop Webhook', [
            'webhook_id' => $webhookId,
            'status'     => $response->status(),
            'response'   => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
