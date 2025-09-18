<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'media_type', // 'image', 'video', 'gif'
        'media_url',
        'thumbnail_url',
        'alt_text',
        'caption',
        'file_size',
        'duration', // for videos
        'width',
        'height',
        'order',
        'is_processed', // for video processing
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'duration' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    public function scopeGifs($query)
    {
        return $query->where('media_type', 'gif');
    }

    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    // Accessors
    public function getIsImageAttribute(): bool
    {
        return $this->media_type === 'image';
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->media_type === 'video';
    }

    public function getIsGifAttribute(): bool
    {
        return $this->media_type === 'gif';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '';
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    // Methods
    public function getOptimizedUrl(string $size = null): string
    {
        if (!$size) {
            return $this->media_url;
        }
        
        // Parse size parameter (e.g., "300x200")
        $dimensions = explode('x', $size);
        if (count($dimensions) === 2) {
            $width = $dimensions[0];
            $height = $dimensions[1];
            
            // Append ImageKit transformation parameters
            $separator = str_contains($this->media_url, '?') ? '&' : '?';
            return $this->media_url . $separator . "tr=w-{$width},h-{$height},c-at_max,fo-auto";
        }
        
        return $this->media_url;
    }

    public function getThumbnailUrl(string $size = '100x100'): string
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }
        
        // Generate thumbnail URL using ImageKit transformations
        return $this->getOptimizedUrl($size);
    }
}
