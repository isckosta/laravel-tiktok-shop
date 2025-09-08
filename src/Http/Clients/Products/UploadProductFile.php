<?php

namespace TikTokShop\Http\Clients\Products;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use TikTokShop\Http\HttpClient;

/**
 * Client para upload de arquivos de produtos.
 *
 * Documentação oficial:
 * @see https://partner.tiktokshop.com/docv2/page/upload-product-file-202309
 *
 * Arquivos suportados:
 * - PDF (até 20 MB)
 * - Vídeos MP4, MOV, MKV, WMV, WEBM, AVI, 3GP, FLV, MPEG (até 100 MB, 9:16 a 16:9)
 */
class UploadProductFile
{
    private const ENDPOINT = '/product/202309/files/upload';

    public function __construct(private HttpClient $http) {}

    /**
     * Envia um arquivo (vídeo, certificado, relatório etc.) para o produto.
     *
     * @param UploadedFile|string $file Instância de UploadedFile, caminho local ou URL
     * @return array{success: bool, code: int|null, message: string|null, data: array}
     */
    public function upload(UploadedFile|string $file): array
    {
        $multipart = $this->buildMultipart($file);

        $response = $this->http->postMultipartWithAuth(self::ENDPOINT, $multipart);
        $decoded  = $response->json() ?? [];

        Log::info('[TikTokShop] Upload de arquivo de produto', [
            'status'   => $response->status(),
            'response' => $decoded,
        ]);

        return $this->formatResponse($decoded);
    }

    /**
     * Decide a estratégia de montagem do multipart com base no tipo de arquivo.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildMultipart(UploadedFile|string $file): array
    {
        if ($file instanceof UploadedFile) {
            return $this->fromUploadedFile($file);
        }

        if (is_string($file) && file_exists($file)) {
            return $this->fromLocalPath($file);
        }

        if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
            return $this->fromRemoteUrl($file);
        }

        throw new \InvalidArgumentException('Arquivo inválido: deve ser UploadedFile, caminho local ou URL.');
    }

    /**
     * Multipart a partir de um UploadedFile (Laravel).
     */
    private function fromUploadedFile(UploadedFile $file): array
    {
        $fileName = $file->getClientOriginalName();

        return [
            $this->namePart($fileName),
            $this->dataPart(fopen($file->getRealPath(), 'r'), $fileName),
        ];
    }

    /**
     * Multipart a partir de um arquivo local.
     */
    private function fromLocalPath(string $path): array
    {
        $fileName = basename($path);

        return [
            $this->namePart($fileName),
            $this->dataPart(fopen($path, 'r'), $fileName),
        ];
    }

    /**
     * Multipart a partir de uma URL remota.
     */
    private function fromRemoteUrl(string $url): array
    {
        $content = file_get_contents($url);
        if ($content === false) {
            throw new \RuntimeException("Erro ao baixar arquivo da URL: {$url}");
        }

        $fileName = basename(parse_url($url, PHP_URL_PATH)) ?: 'file.bin';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        return [
            $this->namePart($fileName),
            $this->dataPart($stream, $fileName),
        ];
    }

    private function namePart(string $fileName): array
    {
        return [
            'name'     => 'name',
            'contents' => $fileName,
        ];
    }

    /**
     * @param resource $stream
     */
    private function dataPart($stream, string $fileName): array
    {
        return [
            'name'     => 'data',
            'contents' => $stream,
            'filename' => $fileName,
        ];
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
            'code'    => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data'    => $decoded['data'] ?? [],
        ];
    }
}
