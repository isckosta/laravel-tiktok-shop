<?php

namespace TikTokShop\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleWebhookJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        // TODO: mapear $this->payload['type'] para eventos internos
        // e persistir/acionar lógica da aplicação
    }
}
