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
        Schema::create('return_order_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('return_orders_id')->nullable();
            // Removed foreign key constraint to avoid transaction issues
            $table->string('image_name')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_order_images');
    }
};
