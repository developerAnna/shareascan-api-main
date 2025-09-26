<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'content',
        'type', // 'text', 'image', 'video', 'poll'
        'is_reply',
        'parent_post_id',
        'is_quote',
        'quoted_post_id',
        'visibility', // 'public', 'followers', 'private'
        'location',
        'qr_code_path',
        'scheduled_at',
        'published_at',
        'status', // 'draft', 'published', 'scheduled', 'deleted'
        'like_count',
        'repost_count',
        'reply_count',
        'is_pinned',
    ];

    protected $casts = [
        'is_reply' => 'boolean',
        'is_quote' => 'boolean',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Removed eager loading to avoid errors with missing tables
    // protected $with = ['user', 'media'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_post_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_post_id');
    }

    public function quotedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'quoted_post_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Post::class, 'quoted_post_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(PostRepost::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function hashtags(): HasMany
    {
        return $this->hasMany(PostHashtag::class);
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(PostMention::class);
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeNotReply($query)
    {
        return $query->where('is_reply', false);
    }

    public function scopeNotQuote($query)
    {
        return $query->where('is_quote', false);
    }

    // Accessors
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getRepostsCountAttribute(): int
    {
        return $this->reposts()->count();
    }

    public function getRepliesCountAttribute(): int
    {
        return $this->replies()->count();
    }

    public function getQuotesCountAttribute(): int
    {
        return $this->quotes()->count();
    }

    public function getBookmarksCountAttribute(): int
    {
        return $this->bookmarks()->count();
    }

    public function getIsLikedByUserAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    public function getIsRepostedByUserAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return $this->reposts()->where('user_id', auth()->id())->exists();
    }

    public function getIsBookmarkedByUserAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return $this->bookmarks()->where('user_id', auth()->id())->exists();
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }
        return asset('storage/' . $this->qr_code_path);
    }

    // Methods
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canBeViewedBy(User $user): bool
    {
        // Public posts can be viewed by anyone
        if ($this->visibility === 'public') {
            return true;
        }

        // Private posts can only be viewed by the owner
        if ($this->visibility === 'private') {
            return $this->isOwnedBy($user);
        }

        // Followers-only posts can be viewed by followers and the owner
        if ($this->visibility === 'followers') {
            return $this->isOwnedBy($user) || $this->user->followers()->where('follower_id', $user->id)->exists();
        }

        return false;
    }
}

