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
        Schema::create('desings', function (Blueprint $table) {
            $table->id();
            $table->string('image_name')->nullable();
            $table->string('image_path')->nullable();
            $table->string('x_axis')->nullable();
            $table->string('y_axis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desings');
    }
};
