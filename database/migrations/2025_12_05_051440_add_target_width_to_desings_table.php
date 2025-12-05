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
        Schema::table('desings', function (Blueprint $table) {
            $table->integer('target_width')->after('y_axis')->nullable();
            $table->integer('target_height')->after('target_width')->nullable();
            $table->integer('rotation')->after('rotation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desings', function (Blueprint $table) {
            $table->dropColumn('target_width');
            $table->dropColumn('target_height');
            $table->dropColumn('rotation');
        });
    }
};
