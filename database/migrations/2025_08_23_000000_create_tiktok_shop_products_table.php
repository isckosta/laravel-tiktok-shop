<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiktok_shop_products', function (Blueprint $table) {
            $table->id();
            $table->string('tiktok_id')->unique();
            $table->string('name');
            $table->string('status')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('shop_cipher')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiktok_products');
    }
};
