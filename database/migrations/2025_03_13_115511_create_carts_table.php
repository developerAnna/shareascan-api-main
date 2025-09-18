<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indicates if the migration should run within a transaction.
     */
    public $withinTransaction = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            // Removed foreign key constraint to avoid transaction issues
            $table->integer('qty')->nullable();
            $table->string('price')->nullable();
            $table->string('total')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_title')->nullable();
            $table->integer('product_variation_id')->nullable();
            $table->string('variation_color')->nullable();
            $table->string('variation_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
