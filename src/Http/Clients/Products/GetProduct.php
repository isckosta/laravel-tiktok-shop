<?php

namespace TikTokShop\Http\Clients\Products;

use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;
use Illuminate\Support\Facades\Log;

/**
 * Client para buscar detalhes de um produto específico.
 *
 * @see https://partner.tiktokshop.com/docv2/page/get-product-202309
 */
class GetProduct
{
    private const ENDPOINT = '/product/202309/products/{product_id}';

    public function __construct(private HttpClient $http) {}

    /**
     * Obtém informações detalhadas de um produto.
     *
     * @param string $productId ID do produto
     * @param bool $returnUnderReviewVersion Retorna versão em revisão (mutuamente exclusivo com $returnDraftVersion)
     * @param bool $returnDraftVersion Retorna versão em rascunho (mutuamente exclusivo com $returnUnderReviewVersion)
     * @param string|null $locale Idioma/locale da resposta (ex: pt-BR, en-US)
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function get(string $productId, bool $returnUnderReviewVersion = false, bool $returnDraftVersion = false, ?string $locale = null): array {

        if ($returnUnderReviewVersion && $returnDraftVersion) {
            throw new \InvalidArgumentException(
                'Os parâmetros returnUnderReviewVersion e returnDraftVersion são mutuamente exclusivos.'
            );
        }

        $query = [];

        if ($returnUnderReviewVersion) {
            $query['return_under_review_version'] = true;
        }

        if ($returnDraftVersion) {
            $query['return_draft_version'] = true;
        }

        if ($locale) {
            $query['locale'] = $locale;
        }

        $endpoint = str_replace('{product_id}', $productId, self::ENDPOINT);

        $resp = $this->http->getWithAuth($endpoint, $query);
        $decoded = $resp->json() ?? [];

        Log::info('[TikTokShop] Get Product', [
            'endpoint' => $endpoint,
            'query'    => $query,
            'status'   => $resp->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }
}
