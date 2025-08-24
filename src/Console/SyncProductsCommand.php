<?php

namespace TikTokShop\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use TikTokShop\Facades\TikTokShop;
use App\Models\Product;
use TikTokShop\Models\TikTokShopProduct;

// ajuste para o seu Model

class SyncProductsCommand extends Command
{
    protected $signature   = 'tiktokshop:sync-products {client_hash=default}';
    protected $description = 'Sincroniza produtos da TikTok Shop para o banco local';

    public function handle(): int
    {
        $clientHash = $this->argument('client_hash');
        $client = TikTokShop::connection($clientHash);

        $cursor = null;
        $pageSize = 50;
        $total = 0;

        $this->info("Iniciando sincronização de produtos para client [{$clientHash}]...");

        do {
            $resp = $client->products()->list(["status" => "ALL"], $cursor, $pageSize);

            $products = $resp['data']['products'] ?? [];
            $cursor = $resp['data']['next_page_token'] ?? null;

            foreach ($products as $prod) {
                TikTokShopProduct::updateOrCreate(
                    ['tiktok_id' => $prod['id']],
                    [
                        'name'        => $prod['title'] ?? '',
                        'status'      => $prod['status'] ?? null,
                        'price'       => $prod['skus'][0]['price']['amount'] ?? 0,
                        'currency'    => $prod['skus'][0]['price']['currency'] ?? 'BRL',
                        'shop_cipher' => $prod['shop_cipher'] ?? null,
                        'raw_data'    => $prod,
                    ]
                );
                $total++;
            }

            $this->line("-> Sincronizados " . count($products) . " produtos (Total: {$total})");

        } while ($cursor);

        $this->info("Finalizado! {$total} produtos sincronizados.");

        return self::SUCCESS;
    }
}
