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
        Schema::table('post_reposts', function (Blueprint $table) {
            $table->longText('repost_text')->nullable();
            $table->longText('reposted_at')->nullable();
            $table->boolean('is_quote')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_reposts', function (Blueprint $table) {
            $table->dropColumn('repost_text');
            $table->dropColumn('reposted_at');
            $table->dropColumn('is_quote');
        });
    }
};
