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
        Schema::create('store_webhooks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->nullable();
            // Removed foreign key constraint to avoid transaction issues
            $table->string('hoook_id')->nullable();
            $table->string('hook_type')->nullable();
            $table->string('hook_status')->nullable();
            $table->string('event_id')->nullable();
            $table->longText('hook_data')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_webhooks');
    }
};
