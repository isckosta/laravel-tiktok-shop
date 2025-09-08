<?php

namespace TikTokShop\Support;

/**
 * Helper para padronizar as respostas da API TikTok Shop.
 */
class ResponseFormatter
{
    /**
     * Normaliza resposta da API em formato unificado.
     *
     * @param array<string, mixed> $decoded
     * @return array{
     *     success: bool,
     *     code: int|null,
     *     message: string|null,
     *     data: array
     * }
     */
    public static function format(array $decoded): array
    {
        return [
            'success' => ($decoded['code'] ?? -1) === 0,
            'code'    => $decoded['code'] ?? null,
            'message' => $decoded['message'] ?? null,
            'data'    => $decoded['data'] ?? [],
        ];
    }
}
