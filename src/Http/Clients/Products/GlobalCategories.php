<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

class GlobalCategories
{
    private const ENDPOINT = '/product/202309/global_categories';

    public function __construct(private HttpClient $http) {}

    /**
     * Obtém categorias globais da TikTok Shop.
     *
     * @param string|null $locale Código de idioma BCP-47 (ex: en-US, de-DE, en-GB, en-IE, en-US, es-ES, es-MX, fr-FR, id-ID, it-IT, ja-JP, ms-MY, th-TH, vi-VN, zh-CN).
     * @param string|null $keyword Filtro por palavra-chave no nome da categoria.
     * @param string $categoryVersion Versão da árvore de categorias (v1 = padrão, v2 = 7 níveis US).
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function list(?string $locale = null, ?string $keyword = null, string $categoryVersion = 'v1'): array
    {
        $query = [
            'category_version' => $categoryVersion,
        ];

        if ($locale) {
            $query['locale'] = $locale;
        }

        if ($keyword) {
            $query['keyword'] = $keyword;
        }

        $resp = $this->http->getWithAuth(self::ENDPOINT, $query, true);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Get Global Categories', [
            'query'    => $query,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
