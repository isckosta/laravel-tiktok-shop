<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;

/**
 * Client para upload de imagens de produtos.
 *
 * @see https://partner.tiktokshop.com/docv2/page/upload-product-image-202309
 *
 * Arquivos suportados:
 *  - Imagens JPG, JPEG, PNG, GIF, WEBP, HEIC, BMP
 *  - Tamanho máximo: 10 MB
 *  - Dimensões: entre 100x100 px e 20000x20000 px
 *
 * Observações:
 *  - Para `use_case=MAIN_IMAGE`, dimensões entre 300x300 px e 4000x4000 px
 *  - Para `use_case=SIZE_CHART_IMAGE`, o lado menor deve ter no mínimo 1024 px
 */
class UploadProductImage
{
    private const IMAGE_UPLOAD_ENDPOINT = '/product/202309/images/upload';

    public function __construct(private HttpClient $http)
    {
    }

    /**
     * Faz upload de uma ou mais imagens para o produto.
     *
     * @param array<int, UploadedFile|string> $filesOrUrls
     * Lista de arquivos locais (UploadedFile), caminhos absolutos ou URLs.
     *
     * @param string $useCase
     * Cenário de uso da imagem. Valores aceitos:
     * - MAIN_IMAGE: imagem exibida na galeria principal do produto
     * - ATTRIBUTE_IMAGE: imagem que representa variante (ex.: cor, modelo)
     * - DESCRIPTION_IMAGE: usada dentro da descrição do produto
     * - CERTIFICATION_IMAGE: imagem de certificações exigidas
     * - SIZE_CHART_IMAGE: imagem com tabela de medidas
     *@return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function upload(array $filesOrUrls, string $useCase = 'MAIN_IMAGE'): array
    {
        $multipart = [
            [
                'name'     => 'use_case',
                'contents' => $useCase,
            ],
        ];

        foreach ($filesOrUrls as $file) {
            $multipart = array_merge($multipart, $this->buildMultipartPart($file));
        }

        $response = $this->http->postMultipartWithAuth(self::IMAGE_UPLOAD_ENDPOINT, $multipart);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Upload de imagens de produto', [
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return $this->formatResponse($decoded);
    }

    /**
     * Normaliza a resposta para um formato unificado.
     *
     * @param array<string, mixed> $decoded
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    private function formatResponse(array $decoded): array
    {
        return [
            'success' => ($decoded['code'] ?? -1) === 0,
            'code' => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data' => $decoded['data'] ?? [],
        ];
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
}
