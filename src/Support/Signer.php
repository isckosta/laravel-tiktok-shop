<?php

namespace TikTokShop\Support;

class Signer
{
    /**
     * Assinatura no formato do SDK Node:
     * sign = HMAC-SHA256(key=app_secret, data= app_secret + (pathname + sortedQuery + optionalJsonBody) + app_secret)
     *
     * - Exclui 'sign' e 'access_token' da query
     * - Ordena por chave (lexicográfica)
     * - Concatena cada par como {key}{value} (sem '=' e sem '&')
     * - Acrescenta o JSON do body (sem espaços) se NÃO for multipart e houver body
     */
    public static function signOpenApi(
        string $pathname,
        array $query,
        array $body,
        string $appSecret,
        bool $isMultipart = false
    ): string {
        // 1) filtra parâmetros inválidos e exclui access_token/sign
        $filtered = [];
        foreach ($query as $k => $v) {
            if ($v === null || $v === '') continue;
            if ($k === 'access_token' || $k === 'sign') continue;
            $filtered[$k] = $v;
        }

        // 2) ordena por chave
        ksort($filtered);

        // 3) concatena {key}{value}
        $paramString = '';
        foreach ($filtered as $k => $v) {
            $paramString .= $k . $v;
        }

        // 4) base: pathname + params + (JSON do body se não for multipart e existir)
        $base = $pathname . $paramString;

        if (! $isMultipart && ! empty($body)) {
            $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $base .= $json;
        }

        // 5) wrap com secret nos dois lados
        $wrapped = $appSecret . $base . $appSecret;

        // 6) HMAC-SHA256 com app_secret como chave
        return hash_hmac('sha256', $wrapped, $appSecret);
    }
}
