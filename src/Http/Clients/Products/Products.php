<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use TikTokShop\Http\Clients\Images\OptimizeProductImage;
use TikTokShop\Http\Clients\Webhooks\UpdateShopWebhook;
use TikTokShop\Http\HttpClient;
use TikTokShop\Support\ResponseFormatter;

/**
 * Client para operações com produtos na TikTok Shop.
 *
 * Documentação oficial:
 * @see https://partner.tiktokshop.com/docv2/page/create-product-202309
 */
class Products
{
    private const CREATE_ENDPOINT       = '/product/202309/products';
    private const GET_PRODUCT_ENDPOINT  = '/product/202309/products/%s';
    private const LIST_ENDPOINT         = '/product/202502/products/search';
    private const IMAGE_UPLOAD_ENDPOINT = '/product/202309/images/upload';
    private const SEARCH_ENDPOINT       = '/product/202502/products/search';

    public function __construct(private HttpClient $http) {}

    /**
     * Cria um novo produto.
     *
     * @param array<string, mixed> $payload
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function create(array $payload): array
    {
        $normalized = $this->normalizePackageData($payload);

        $response = $this->http->postWithAuth(self::CREATE_ENDPOINT, $normalized);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Criação de produto', [
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    /**
     * Lista produtos do catálogo.
     *
     * @param array<string, mixed> $filters
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function list(array $filters = [], ?string $cursor = null, int $pageSize = 20): array
    {
        $query = ['page_size' => $pageSize];
        if ($cursor) {
            $query['page_token'] = $cursor;
        }

        $response = $this->http->postWithAuth(self::LIST_ENDPOINT, $filters, $query);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Listagem de produtos', [
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    /**
     * Obtém detalhes de um produto.
     *
     * @param string $productId ID do produto
     * @param bool $returnUnderReviewVersion Retorna versão em revisão
     * @param bool $returnDraftVersion Retorna versão em rascunho
     * @param string|null $locale Idioma da resposta (ex: pt-BR, en-US)
     */
    public function getProduct(
        string $productId,
        bool $returnUnderReviewVersion = false,
        bool $returnDraftVersion = false,
        ?string $locale = null
    ): array {
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

        $endpoint = sprintf(self::GET_PRODUCT_ENDPOINT, $productId);

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

    /**
     * Busca produtos no catálogo com filtros avançados.
     *
     * @param array<string, mixed> $filters Filtros opcionais (status, seller_skus, audit_status, sku_ids, create_time_ge, update_time_ge, etc.)
     * @param int $pageSize Quantidade de resultados por página (1–100)
     * @param string|null $cursor Token de paginação
     *
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function search(array $filters = [], int $pageSize = 20, ?string $cursor = null): array
    {
        $query = ['page_size' => $pageSize];
        if ($cursor) {
            $query['page_token'] = $cursor;
        }

        // Remove filtros nulos
        $body = array_filter($filters, fn($value) => $value !== null);

        $response = $this->http->postWithAuth(self::SEARCH_ENDPOINT, $body, $query);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Search Products', [
            'query'    => $query,
            'body'     => $body,
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    /**
     * Upload de imagens para produto.
     *
     * @param array<int, UploadedFile|string> $filesOrUrls
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function uploadImages(array $filesOrUrls): array
    {
        $multipart = array_map(fn ($file) => $this->buildMultipartPart($file), $filesOrUrls);
        $multipart = array_merge(...$multipart);

        $response = $this->http->postMultipartWithAuth(self::IMAGE_UPLOAD_ENDPOINT, $multipart);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Upload de imagens de produto', [
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return ResponseFormatter::format($decoded);
    }

    // ------------------------
    // Métodos privados de apoio
    // ------------------------

    /**
     * Normaliza dados de pacote no formato esperado pela API.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePackageData(array $payload): array
    {
        if (isset($payload['package_weight']) && is_numeric($payload['package_weight'])) {
            $payload['package_weight'] = [
                'value' => (string) $payload['package_weight'],
                'unit'  => 'GRAM',
            ];
        }

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

    /**
     * Cria as partes multipart de um arquivo (local, UploadedFile ou URL).
     *
     * @param UploadedFile|string $file
     * @return array<int, array<string, mixed>>
     */
    private function buildMultipartPart(UploadedFile|string $file): array
    {
        if ($file instanceof UploadedFile) {
            return [[
                'name'     => 'data',
                'contents' => fopen($file->getRealPath(), 'r'),
                'filename' => $file->getClientOriginalName(),
            ]];
        }

        if (is_string($file) && file_exists($file)) {
            return [[
                'name'     => 'data',
                'contents' => fopen($file, 'r'),
                'filename' => basename($file),
            ]];
        }

        if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
            $content = file_get_contents($file);
            if ($content === false) {
                throw new \RuntimeException("Erro ao baixar imagem da URL: {$file}");
            }

            $fileName = basename(parse_url($file, PHP_URL_PATH)) ?: 'image.jpg';
            $stream   = fopen('php://temp', 'r+');
            fwrite($stream, $content);
            rewind($stream);

            return [[
                'name'     => 'data',
                'contents' => $stream,
                'filename' => $fileName,
            ]];
        }

        throw new \InvalidArgumentException('Arquivo inválido: deve ser UploadedFile, caminho local ou URL.');
    }

    public function files(): UploadProductFile
    {
        return new UploadProductFile($this->http);
    }

    public function images(): UploadProductImage
    {
        return new UploadProductImage($this->http);
    }

    public function partialEdit(): PartialEditProduct
    {
        return new PartialEditProduct($this->http);
    }

    public function editProduct(): EditProduct
    {
        return new EditProduct($this->http);
    }

    public function activateProducts(): ActivateProduct
    {
        return new ActivateProduct($this->http);
    }

    public function deactivateProducts(): DeactivateProducts
    {
        return new DeactivateProducts($this->http);
    }

    public function deleteProducts(): DeleteProducts
    {
        return new DeleteProducts($this->http);
    }

    public function recoverProducts(): RecoverProducts
    {
        return new RecoverProducts($this->http);
    }

    public function prices(): UpdateProductPrices
    {
        return new UpdateProductPrices($this->http);
    }

    public function inventory(): UpdateProductInventory
    {
        return new UpdateProductInventory($this->http);
    }

    public function inventorySearch(): SearchInventory
    {
        return new SearchInventory($this->http);
    }

    public function diagnoseOptimize(): DiagnoseOptimizeProduct
    {
        return new DiagnoseOptimizeProduct($this->http);
    }

    public function diagnoseIssues(): DiagnoseProductIssues
    {
        return new DiagnoseProductIssues($this->http);
    }

    public function seoWords(): GetProductsSeoWords
    {
        return new GetProductsSeoWords($this->http);
    }

    public function suggestions(): ProductSuggestions
    {
        return new ProductSuggestions($this->http);
    }

    public function optimizeImages(): OptimizeProductImage
    {
        return new OptimizeProductImage($this->http);
    }

    public function globalCategories(): GlobalCategories
    {
        return new GlobalCategories($this->http);
    }
}
