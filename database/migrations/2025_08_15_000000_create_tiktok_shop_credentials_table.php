<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tiktok_shop_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('client_hash')->index();
            $table->string('shop_cipher')->nullable();
            $table->string('app_key');
            $table->string('app_secret');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamps();
            $table->unique(['client_hash', 'shop_cipher']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_shop_credentials');
    }
};
