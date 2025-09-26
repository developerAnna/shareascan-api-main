<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostRepost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'repost_text', // Optional comment when reposting
        'reposted_at',
        'is_quote', // Whether it's a quote repost with additional text
    ];

    protected $casts = [
        'reposted_at' => 'datetime',
        'is_quote' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeQuotes($query)
    {
        return $query->where('is_quote', true);
    }

    public function scopeReposts($query)
    {
        return $query->where('is_quote', false);
    }

    // Methods
    public static function toggleRepost($userId, $postId, $repostText = null): bool
    {
        $existingRepost = static::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($existingRepost) {
            $existingRepost->delete();
            return false; // Repost removed
        }

        static::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'repost_text' => $repostText,
            'reposted_at' => now(),
            'is_quote' => !empty($repostText),
        ]);

        return true; // Repost added
    }

    public static function hasUserReposted($userId, $postId): bool
    {
        return static::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    public static function getUserRepost($userId, $postId)
    {
        return static::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();
    }
}

