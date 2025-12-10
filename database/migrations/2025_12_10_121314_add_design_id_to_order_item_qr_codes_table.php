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
        Schema::table('order_item_qr_codes', function (Blueprint $table) {
            $table->bigInteger('design_id')->nullable();
            $table->string('desing_image')->nullable();
            $table->string('desing_file_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item_qr_codes', function (Blueprint $table) {
            $table->dropColumn('design_id');
            $table->dropColumn('desing_image');
            $table->dropColumn('desing_file_path');
        });
    }
};
