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
        // First, add new columns
        Schema::table('posts', function (Blueprint $table) {
            // Add missing columns that the Post model expects
            $table->string('visibility', 20)->default('public'); // public, followers, private
            $table->string('status', 20)->default('published'); // draft, published, scheduled, deleted
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_reply')->default(false);
            $table->bigInteger('parent_post_id')->nullable(); // Replaces reply_to_post_id
            $table->boolean('is_quote')->default(false);
            $table->bigInteger('quoted_post_id')->nullable(); // Replaces repost_of_post_id
            $table->string('location')->nullable();
            $table->softDeletes(); // Adds deleted_at column
        });
        
        // Then drop columns in a separate statement
        Schema::table('posts', function (Blueprint $table) {
            // Drop columns that are being replaced
            $table->dropColumn(['reply_to_post_id', 'repost_of_post_id', 'is_published']);
            
            // Rename columns to match model expectations
            // media_urls, hashtags, mentions are handled differently in the new structure
            $table->dropColumn(['media_urls', 'hashtags', 'mentions']);
        });

        // Update post_media table structure if it exists
        if (Schema::hasTable('post_media')) {
            Schema::table('post_media', function (Blueprint $table) {
                // Add missing columns
                $table->string('media_type', 20)->default('image')->after('post_id'); // image, video
                $table->string('media_url')->after('media_type');
                $table->string('thumbnail_url')->nullable()->after('media_url');
                $table->string('alt_text')->nullable()->after('thumbnail_url');
                $table->string('caption', 500)->nullable()->after('alt_text');
                $table->integer('width')->nullable()->after('file_size');
                $table->integer('height')->nullable()->after('width');
                $table->integer('order')->default(0)->after('height');
                $table->boolean('is_processed')->default(true)->after('order');
                
                // Drop or rename existing columns if needed
                if (Schema::hasColumn('post_media', 'file_path')) {
                    $table->dropColumn('file_path');
                }
                if (Schema::hasColumn('post_media', 'file_type')) {
                    $table->dropColumn('file_type');
                }
                if (Schema::hasColumn('post_media', 'metadata')) {
                    $table->dropColumn('metadata');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Revert changes
            $table->dropColumn(['visibility', 'status', 'published_at', 'is_reply', 'parent_post_id', 'is_quote', 'quoted_post_id', 'location']);
            $table->dropSoftDeletes();
            
            // Re-add original columns
            $table->bigInteger('reply_to_post_id')->nullable();
            $table->bigInteger('repost_of_post_id')->nullable();
            $table->boolean('is_published')->default(true);
            $table->json('media_urls')->nullable();
            $table->json('hashtags')->nullable();
            $table->json('mentions')->nullable();
        });

        if (Schema::hasTable('post_media')) {
            Schema::table('post_media', function (Blueprint $table) {
                $table->dropColumn(['media_type', 'media_url', 'thumbnail_url', 'alt_text', 'caption', 'width', 'height', 'order', 'is_processed']);
                $table->string('file_path');
                $table->string('file_type');
                $table->json('metadata')->nullable();
            });
        }
    }
};
