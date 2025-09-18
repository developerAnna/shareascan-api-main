<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'ratings' => $this->star_count,
            'content' => $this->content,
            'user_name' => $this->user ? $this->user->name .' '. $this->user->last_name : null,
            'images' => $this->reviewImages ? $this->reviewImages->map(function ($image) {
                return [
                    'filename' => $image->filename,
                    'file_path' => asset('storage/' . $image->file_path), // Concatenate the full URL to the path
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ];
            }) : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
