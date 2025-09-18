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
        // Skip this migration - All tables are already created in other migrations
        return;
        
        // Create all basic tables in one migration to avoid transaction issues
        
        // Users table - Skip since it's already created in 0001_01_01_000000_create_users_table
        // Schema::create('users', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('email');
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->string('provider')->nullable();
        //     $table->string('provider_id')->nullable();
        //     $table->string('last_name')->nullable();
        //     $table->string('remember_token', 100)->nullable();
        //     $table->timestamps();
        // });

        // Cache table - Skip since it's already created
        // Schema::create('cache', function (Blueprint $table) {
        //     $table->string('key');
        //     $table->text('value');
        //     $table->integer('expiration');
        // });

        // Cache locks table - Skip since it's already created
        // Schema::create('cache_locks', function (Blueprint $table) {
        //     $table->string('key');
        //     $table->string('owner');
        //     $table->integer('expiration');
        // });

        // Jobs table - Skip since it's already created
        // Schema::create('jobs', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('queue');
        //     $table->text('payload');
        //     $table->smallInteger('attempts');
        //     $table->integer('reserved_at')->nullable();
        //     $table->integer('available_at');
        //     $table->integer('created_at');
        // });

        // Job batches table
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id');
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->text('failed_job_ids');
            $table->text('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Failed jobs table
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->text('connection');
            $table->text('queue');
            $table->text('payload');
            $table->text('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Admins table
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        // Settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });

        // Categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        // FAQs table
        Schema::create('f_a_q_s', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        // Email templates table
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('content');
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        // Product settings table
        Schema::create('product_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });

        // Personal access tokens table
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->bigInteger('tokenable_id');
            $table->string('name');
            $table->string('token', 64);
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // User details table
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zipcode')->nullable();
            $table->timestamps();
        });

        // Wishlists table
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->timestamps();
        });

        // Reviews table
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('status')->nullable();
            $table->integer('star_count')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });

        // Review images table
        Schema::create('review_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('review_id')->nullable();
            $table->string('image');
            $table->timestamps();
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });

        // Password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('f_a_q_s');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('product_settings');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('review_images');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
    }
};
