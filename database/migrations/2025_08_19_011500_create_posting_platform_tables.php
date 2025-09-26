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
        // Create all tables for the Twitter-style posting platform
        
        // Users table (core user management) - Skip since it's already created
        // Schema::create('users', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('email');
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->string('username')->unique();
        //     $table->text('bio')->nullable();
        //     $table->string('avatar')->nullable();
        //     $table->string('cover_image')->nullable();
        //     $table->string('location')->nullable();
        //     $table->string('website')->nullable();
        //     $table->date('birth_date')->nullable();
        //     $table->boolean('is_verified')->default(false);
        //     $table->boolean('is_private')->default(false);
        //     $table->string('remember_token', 100)->nullable();
        //     $table->timestamps();
        // });

        // Posts table (main posting functionality)
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->text('content');
            $table->string('type')->default('text'); // text, image, video, poll
            $table->json('media_urls')->nullable(); // Store multiple media URLs
            $table->json('hashtags')->nullable(); // Store hashtags as array
            $table->json('mentions')->nullable(); // Store user mentions
            $table->bigInteger('reply_to_post_id')->nullable(); // For replies
            $table->bigInteger('repost_of_post_id')->nullable(); // For reposts
            $table->integer('like_count')->default(0);
            $table->integer('repost_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Post likes table
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('post_id');
            $table->timestamps();
        });

        // Post reposts table
        Schema::create('post_reposts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('post_id');
            $table->text('comment')->nullable(); // Optional comment on repost
            $table->timestamps();
        });

        // Post media table
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('post_id');
            $table->string('file_path');
            $table->string('file_type'); // image, video, gif
            $table->string('mime_type');
            $table->integer('file_size');
            $table->json('metadata')->nullable(); // Store dimensions, duration, etc.
            $table->timestamps();
        });

        // User follows table (following/followers)
        Schema::create('user_follows', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('follower_id'); // User doing the following
            $table->bigInteger('following_id'); // User being followed
            $table->boolean('is_mutual')->default(false);
            $table->timestamps();
        });

        // User blocks table
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('blocker_id'); // User doing the blocking
            $table->bigInteger('blocked_id'); // User being blocked
            $table->timestamps();
        });

        // User mutes table
        Schema::create('user_mutes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('muter_id'); // User doing the muting
            $table->bigInteger('muted_id'); // User being muted
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Lists table (for creating lists of users)
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id'); // Creator of the list
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->integer('member_count')->default(0);
            $table->integer('subscriber_count')->default(0);
            $table->timestamps();
        });

        // List members table
        Schema::create('list_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('list_id');
            $table->bigInteger('user_id');
            $table->timestamps();
        });

        // List subscribers table
        Schema::create('list_subscribers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('list_id');
            $table->bigInteger('user_id');
            $table->timestamps();
        });

        // Bookmarks table
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('post_id');
            $table->timestamps();
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id'); // Recipient
            $table->bigInteger('from_user_id')->nullable(); // Sender
            $table->string('type'); // like, repost, reply, follow, mention
            $table->bigInteger('post_id')->nullable(); // Related post
            $table->text('message');
            $table->json('data')->nullable(); // Additional data
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Hashtags table
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('post_count')->default(0);
            $table->timestamps();
        });

        // Trending topics table
        Schema::create('trending_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->integer('post_count');
            $table->integer('trend_score');
            $table->timestamp('trending_since');
            $table->timestamps();
        });

        // Polls table (for poll posts)
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('post_id');
            $table->string('question');
            $table->json('options'); // Array of poll options
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_multiple_choice')->default(false);
            $table->timestamps();
        });

        // Poll votes table
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('poll_id');
            $table->bigInteger('user_id');
            $table->integer('option_index'); // Which option was voted for
            $table->timestamps();
        });

        // Reports table (for reporting posts/users)
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reporter_id'); // User making the report
            $table->bigInteger('reported_user_id')->nullable(); // Reported user
            $table->bigInteger('reported_post_id')->nullable(); // Reported post
            $table->string('reason');
            $table->text('details')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, resolved
            $table->timestamps();
        });

        // Sessions table (for user sessions)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
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
        Schema::dropIfExists('reports');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('trending_topics');
        Schema::dropIfExists('hashtags');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('list_subscribers');
        Schema::dropIfExists('list_members');
        Schema::dropIfExists('lists');
        Schema::dropIfExists('user_mutes');
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('user_follows');
        Schema::dropIfExists('post_media');
        Schema::dropIfExists('post_reposts');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
    }
};

