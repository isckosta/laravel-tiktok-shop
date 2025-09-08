<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas ao cliente HTTP usado para chamadas à API
    | da TikTok Shop. Inclui a URL base para requisições e o timeout padrão.
    | É possível sobrescrever esse timeout em casos específicos (ex.: sync de catálogo).
    |
    */
    'http' => [
        // URL base da API principal da TikTok Shop
        'base_uri' => env('TTSHOP_BASE_URI', 'https://open-api.tiktokglobalshop.com'),

        // Timeout padrão das requisições (em segundos)
        'timeout'  => (int) env('TTSHOP_HTTP_TIMEOUT', 30),

        // Timeout específico para operações pesadas (ex.: sincronização de catálogo)
        'catalog_timeout' => (int) env('TTSHOP_CATALOG_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Connection
    |--------------------------------------------------------------------------
    |
    | Define a conexão padrão para operações. Esse valor pode ser usado para
    | multi-tenant, identificando lojas diferentes por "client_hash".
    |
    */
    'default_connection' => env('TTSHOP_DEFAULT_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para recebimento e validação de webhooks enviados pela
    | TikTok Shop. Inclui a rota registrada automaticamente, o header usado
    | para verificar a assinatura e o segredo de validação.
    |
    | Também é possível definir uma fila dedicada para processar eventos.
    |
    */
    'webhooks' => [
        // Endpoint onde os webhooks da TikTok Shop serão recebidos
        'route'            => '/webhooks/tiktok-shop',

        // Header enviado pela TikTok que contém a assinatura HMAC
        'signature_header' => 'X-Tt-Signature',

        // Segredo usado para validar a assinatura dos webhooks
        'secret'           => env('TTSHOP_WEBHOOK_SECRET', ''),

        // Fila dedicada para processamento assíncrono de webhooks
        'queue'            => env('TTSHOP_WEBHOOK_QUEUE', 'tiktok-webhooks'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization & OAuth
    |--------------------------------------------------------------------------
    |
    | Configurações usadas no fluxo OAuth com a TikTok Shop.
    | Inclui credenciais do app, URIs de autenticação, redirect callback e
    | refresh_skew para antecipar a renovação do token.
    |
    */
    'auth' => [
        // URL base para autenticação (geralmente auth.tiktok-shops.com)
        'base_uri'     => env('TTSHOP_AUTH_BASE_URI', 'https://auth.tiktok-shops.com'),

        // Service ID fornecido pela TikTok Shop Developer Console
        'service_id'   => env('TTSHOP_SERVICE_ID'),

        // Chave pública da aplicação
        'app_key'      => env('TTSHOP_APP_KEY'),

        // Segredo privado da aplicação
        'app_secret'   => env('TTSHOP_APP_SECRET'),

        // Endpoint de callback registrado no console da TikTok
        'redirect_uri' => env('TTSHOP_REDIRECT_URI', 'http://localhost/tiktok/callback'),

        // Skew (em segundos) para antecipar o refresh do token
        'refresh_skew' => (int) env('TTSHOP_REFRESH_SKEW', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment (Sandbox / Production)
    |--------------------------------------------------------------------------
    |
    | Define o ambiente que será utilizado. Pode ser "production" ou "sandbox".
    | Em sandbox, as requisições podem ir para endpoints de teste da TikTok Shop.
    |
    */
    'environment' => env('TTSHOP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    |
    | Define a versão da API que será utilizada. Isso permite atualizar a versão
    | em um único lugar sem alterar o código de chamadas.
    |
    */
    'api_version' => env('TTSHOP_API_VERSION', '202309'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Permite habilitar/desabilitar logs detalhados das requisições e respostas
    | da TikTok Shop. O canal pode ser configurado para enviar logs para
    | arquivos, Slack, Sentry, etc.
    |
    */
    'logging' => [
        'enabled' => env('TTSHOP_LOGGING_ENABLED', true),
        'channel' => env('TTSHOP_LOGGING_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retries & Backoff
    |--------------------------------------------------------------------------
    |
    | Configurações para tentativas automáticas de retry em caso de falha de rede
    | ou limite de taxa da API. Útil para tornar integrações mais resilientes.
    |
    */
    'retries' => [
        'enabled'      => env('TTSHOP_RETRIES_ENABLED', true),
        'max_attempts' => (int) env('TTSHOP_RETRIES', 3),
        'delay'        => (int) env('TTSHOP_RETRIES_DELAY', 200), // em ms
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Define se resultados de algumas chamadas (ex.: categorias, marcas, tokens)
    | devem ser armazenados em cache para melhorar performance.
    |
    */
    'cache' => [
        'enabled' => env('TTSHOP_CACHE_ENABLED', true),
        'ttl'     => (int) env('TTSHOP_CACHE_TTL', 3600), // em segundos
        'store'   => env('TTSHOP_CACHE_STORE', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Define se o package deve disparar eventos do Laravel, como
    | TikTokShopOrderCreated, TikTokShopProductSynced, etc.
    |
    */
    'events' => [
        'enabled' => env('TTSHOP_EVENTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Jobs & Queues
    |--------------------------------------------------------------------------
    |
    | Permite configurar filas dedicadas para operações pesadas (ex.: sync de catálogo).
    |
    */
    'queue' => [
        'connection'   => env('TTSHOP_QUEUE_CONNECTION', 'redis'),
        'catalog_sync' => env('TTSHOP_SYNC_CATALOG_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Permite configurar notificações em caso de falhas críticas (ex.: token expirado
    | sem refresh). Pode ser integrado com Mail, Slack, Discord etc. via Laravel Notifications.
    |
    */
    'notifications' => [
        'enabled'  => env('TTSHOP_NOTIFICATIONS_ENABLED', false),
        'channels' => explode(',', env('TTSHOP_NOTIFICATIONS_CHANNELS', 'slack')),
    ],
];
