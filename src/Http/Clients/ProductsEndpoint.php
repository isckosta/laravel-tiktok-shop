<?php

namespace TikTokShop\Http\Clients;

use TikTokShop\Http\HttpClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductsEndpoint
{
    public function __construct(private HttpClient $http) {}

    public function create(array $payload): array
    {
        // 🔹 Normalizar peso e dimensões
        $payload = $this->normalizePackageData($payload);

        $resp = $this->http->postWithAuth('/product/202309/products', $payload)->throw();

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao criar produto', [
                'payload'  => $payload,
                'response' => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao criar produto na TikTok Shop.');
        }

        return $resp->json('data') ?? [];
    }

    private function normalizePackageData(array $payload): array
    {
        // Peso
        if (isset($payload['package_weight']) && is_numeric($payload['package_weight'])) {
            $payload['package_weight'] = [
                'value' => (string) $payload['package_weight'], // força string
                'unit'  => 'GRAM', // 🇧🇷 padrão Brasil
            ];
        }

        // Dimensões
        foreach (['package_length', 'package_width', 'package_height'] as $dim) {
            if (isset($payload[$dim]) && is_numeric($payload[$dim])) {
                $payload[$dim] = [
                    'value' => (string) $payload[$dim],
                    'unit'  => 'CENTIMETER',
                ];
            }
        }

        return $payload;
    }

    public function list(array $filters = [], ?string $cursor = null, int $pageSize = 20): array
    {
        $query = [
            'page_size' => $pageSize,
        ];

        if ($cursor) {
            $query['page_token'] = $cursor;
        }

        $body = $filters;

        $resp = $this->http->postWithAuth('/product/202502/products/search', $body, $query)->throw();

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao buscar produtos', [
                'filters'   => $filters,
                'query'     => $query,
                'response'  => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao buscar produtos na TikTok Shop.');
        }

        return $resp->json() ?? [];
    }

    public function uploadImages(array $filesOrUrls): array
    {
        $parts = [];

        foreach ($filesOrUrls as $index => $file) {
            if ($file instanceof UploadedFile) {
                $parts[] = [
                    'name'     => 'image'.($index + 1),
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            } elseif (filter_var($file, FILTER_VALIDATE_URL)) {
                $parts[] = [
                    'name'     => 'image_urls[]',
                    'contents' => $file,
                ];
            }
        }

        $resp = $this->http->postMultipart('/api/products/202309/upload_imgs', $parts);

        if ($resp->failed()) {
            Log::error('[TikTokShop] Falha no upload de imagem', [
                'response' => $resp->body()
            ]);
            throw new \RuntimeException('Erro ao fazer upload de imagem para a TikTok Shop.');
        }

        return $resp->json();
    }
}
