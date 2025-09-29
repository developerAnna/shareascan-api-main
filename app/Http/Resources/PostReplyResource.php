<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostReplyResource extends JsonResource
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
            'post_id' => $this->post_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                // add other user fields as needed
            ],
            'content' => $this->content,
            'parent_reply_id' => $this->parent_reply_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'children' => PostReplyResource::collection($this->whenLoaded('children')),
        ];
    }
}
