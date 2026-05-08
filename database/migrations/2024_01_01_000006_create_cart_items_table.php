<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('cart_uuid');
            $table->uuid('product_uuid');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->foreign('cart_uuid')
                ->references('uuid')
                ->on('carts')
                ->onDelete('cascade');

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};