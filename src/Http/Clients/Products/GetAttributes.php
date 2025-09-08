<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

class GetAttributes
{
    public function __construct(private HttpClient $http) {}

    /**
     * Lista os atributos de uma categoria na TikTok Shop
     *
     * @param int|string $categoryId  ID da categoria
     * @param string     $locale      Idioma (ex: pt-BR, en-US)
     */
    public function list(int|string $categoryId, string $locale = 'pt-BR'): array
    {
        $body = [
            'locale' => $locale,
        ];

        $resp = $this->http
            ->getWithAuth("/product/202309/categories/{$categoryId}/attributes", $body)
            ->throw();

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao buscar atributos da categoria', [
                'category_id' => $categoryId,
                'locale'      => $locale,
                'response'    => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao buscar atributos da categoria na TikTok Shop.');
        }

        return $resp->json('data') ?? [];
    }
}
