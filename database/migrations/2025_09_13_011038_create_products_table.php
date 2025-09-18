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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->json('images')->nullable(); // Array of additional images
            $table->json('variations')->nullable(); // JSON field for size/color variations
            $table->boolean('status')->default(true);
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('meta_description')->nullable();
            $table->json('tags')->nullable(); // Array of tags
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('dimensions')->nullable(); // e.g., "10x5x2 inches"
            $table->timestamps();

            // Foreign key and indexes can be added later if needed
            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            // $table->index(['status', 'featured']);
            // $table->index(['category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
