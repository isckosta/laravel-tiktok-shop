<?php

namespace TikTokShop\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('tiktokshop.webhooks.secret');
        $header = $request->header(config('tiktokshop.webhooks.signature_header'));

        if (! $secret || ! $header) {
            abort(401, 'Missing webhook secret or signature');
        }

        // Observação: ajuste a verificação conforme o algoritmo oficial
        $valid = hash_equals(base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true)), $header);

        if (! $valid) {
            abort(401, 'Invalid signature');
        }

        dispatch(new \TikTokShop\Jobs\HandleWebhookJob($request->all()));

        return response()->json(['ok' => true]);
    }
}
