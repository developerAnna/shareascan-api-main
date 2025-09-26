<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('variation_type'); // 'size', 'color', 'material', etc.
            $table->string('variation_value'); // 'M', 'Red', 'Cotton', etc.
            $table->decimal('price_adjustment', 8, 2)->default(0.00); // Additional cost
            $table->integer('stock_quantity')->default(0);
            $table->string('sku_suffix')->nullable(); // Add to main product SKU
            $table->string('image_url')->nullable(); // Variation-specific image
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'variation_type']);
            $table->unique(['product_id', 'variation_type', 'variation_value'], 'product_variation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
