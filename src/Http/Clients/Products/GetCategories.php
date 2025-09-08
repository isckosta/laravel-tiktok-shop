<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;

class GetCategories
{
    public function __construct(private HttpClient $http) {}

    /**
     * Lista categorias do TikTok Shop.
     * @param string|null $parentId Se informado, retorna subcategorias de uma categoria específica.
     */
    public function list(?string $parentId = null): array
    {
        $query = [];

        if ($parentId) {
            $query['parent_id'] = $parentId;
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
}
