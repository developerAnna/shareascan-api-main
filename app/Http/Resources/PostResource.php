<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'type' => $this->type,
            'visibility' => $this->visibility,
            'location' => $this->location,
            'qr_code_url' => $this->qr_code_url,
            'status' => $this->status,
            'is_reply' => $this->is_reply,
            'is_quote' => $this->is_quote,
            'parent_post_id' => $this->parent_post_id,
            'quoted_post_id' => $this->quoted_post_id,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // User information - only if relationship is loaded
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            // Media attachments - only if relationship is loaded
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'media_type' => $media->media_type,
                        'media_url' => $media->media_url,
                        'thumbnail_url' => $media->thumbnail_url,
                        'alt_text' => $media->alt_text,
                        'caption' => $media->caption,
                        'order' => $media->order,
                        'is_processed' => $media->is_processed,
                    ];
                });
            }),

            // Fetching likes count using the PostLikes relationship
            'likes_count' => $this->whenLoaded('likes', function () {
                return $this->likes()->count(); // Fetch the count of likes from the post_likes table
            }),

            // Reposts count - Count the number of reposts for this post
            'reposts_count' => $this->whenLoaded('reposts', function () {
                return $this->reposts->count(); // Fetch the count of reposts related to this post
            }),



            // Basic counts - set to 0 for now since tables don't exist
            // 'likes_count' => 0,
            // 'reposts_count' => 0,
            'replies_count' => 0,
            'quotes_count' => 0,
            'bookmarks_count' => 0,

            // User interaction status - set to false for now
            // Check if the current authenticated user has liked the post
            'is_liked_by_user' => $this->when(Auth::check(), function () {
                return $this->likes->contains('user_id', Auth::id());
            }, false),
            // 'is_liked_by_user' => false,
            // 'is_reposted_by_user' => false,
            // Check if the current authenticated user has reposted the post
            'is_reposted_by_user' => $this->when(Auth::check(), function () {
                return $this->reposts->contains('user_id', Auth::id());  // Check if the current user has reposted
            }, false),
            'is_bookmarked_by_user' => false,
        ];
    }
}
