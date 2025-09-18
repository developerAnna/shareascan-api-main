<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'liked_at',
    ];

    protected $casts = [
        'liked_at' => 'datetime',
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

    // Methods
    public static function toggleLike($userId, $postId): bool
    {
        $existingLike = static::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return false; // Like removed
        }

        static::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'liked_at' => now(),
        ]);

        return true; // Like added
    }

    public static function hasUserLiked($userId, $postId): bool
    {
        return static::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }
}

