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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            // Removed foreign key constraint to avoid transaction issues
            $table->string('transaction_id')->nullable();
            $table->longText('charge_succeed_res')->nullable();
            $table->longText('cancle_res')->nullable();
            $table->longText('refund_res')->nullable();
            $table->longText('stripe_response')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
