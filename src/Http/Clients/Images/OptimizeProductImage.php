<?php

namespace TikTokShop\Http\Clients\Images;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

class OptimizeProductImage
{
    private const ENDPOINT = '/product/202404/images/optimize';

    public function __construct(private HttpClient $http) {}

    /**
     * Otimiza imagens de produto (ex: fundo branco).
     *
     * @param array<int, array<string, string>> $images
     * Exemplo:
     * [
     *   [
     *     'uri' => 'tos-maliva-i-1234567890abcdef',
     *     'optimization_mode' => 'WHITE_BACKGROUND'
     *   ]
     * ]
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function optimize(array $images): array
    {
        if (count($images) > 200) {
            throw new \InvalidArgumentException('Max 200 images are allowed.');
        }

        $payload = ['images' => $images];

        $resp = $this->http->postWithAuth(self::ENDPOINT, $payload);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Optimize Images', [
            'payload'  => $payload,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
