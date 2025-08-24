<?php

namespace TikTokShop\Http\Clients\Categories;

use TikTokShop\Http\HttpClient;
use Illuminate\Support\Facades\Log;

class CategoriesEndpoint
{
    public function __construct(private HttpClient $http) {}

    /**
     * Lista categorias do TikTok Shop.
     *
     * @param string|null $parentId Se informado, retorna subcategorias de uma categoria específica.
     */
    public function list(?string $parentId = null): array
    {
        $query = [];

        if ($parentId) {
            $query['category_id'] = $parentId;
        }

        $resp = $this->http->getWithAuth('/product/202309/categories', $query)->throw();

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao buscar categorias', [
                'parent_id' => $parentId,
                'response'  => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao buscar categorias da TikTok Shop.');
        }

        return $resp->json('data.categories') ?? [];
    }

    /**
     * Recomenda categoria para um produto.
     *
     * @param string $title       Título do produto
     * @param string $description Descrição do produto
     */
    public function recommend(string $title, string $description): array
    {
        $body = [
            'product_title'       => $title,
            'product_description' => $description,
        ];

        $resp = $this->http->postWithAuth('/product/202309/categories/recommend', $body)->throw();

        if ($resp->failed()) {
            Log::error('[TikTokShop] Erro ao recomendar categoria', [
                'title'       => $title,
                'description' => $description,
                'response'    => $resp->body(),
            ]);
            throw new \RuntimeException('Erro ao recomendar categoria da TikTok Shop.');
        }

        return $resp->json('data.categories') ?? [];
    }
}
