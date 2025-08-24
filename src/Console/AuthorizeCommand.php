<?php

namespace TikTokShop\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthorizeCommand extends Command
{
    protected $signature = 'tiktokshop:authorize {client_hash=default}';
    protected $description = 'Generate the TikTok Shop authorization URL for a given client_hash.';

    public function handle(): int
    {
        $clientHash = $this->argument('client_hash');
        $serviceId  = config('tiktokshop.auth.service_id');
        $appKey     = config('tiktokshop.auth.app_key');
        $redirect   = config('tiktokshop.auth.redirect_uri');
        $authBase   = rtrim(config('tiktokshop.auth.base_uri'), '/');

        if (! $serviceId || ! $redirect || ! $appKey) {
            $this->error('Missing TTSHOP_SERVICE_ID, TTSHOP_AUTH_APP_KEY or TTSHOP_AUTH_REDIRECT_URI in .env/config.');
            return self::FAILURE;
        }

        $state = Str::random(40);
        Cache::put("ttshop:oauth:state:{$state}", ['client_hash' => $clientHash], now()->addMinutes(10));

        $url = "{$authBase}/oauth/authorize"
            . "?service_id={$serviceId}"
            . "&app_key={$appKey}" // ✅ INCLUIR AQUI
            . "&state={$state}"
            . "&redirect_uri=" . urlencode($redirect);

        $this->info("\nAuthorization URL for client_hash '{$clientHash}':\n");
        $this->line($url);
        $this->newLine();

        return self::SUCCESS;
    }
}
