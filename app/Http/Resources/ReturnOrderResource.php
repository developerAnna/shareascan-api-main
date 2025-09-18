<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnOrderResource extends JsonResource
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
            'order_id' => $this->order_id,
            'reason' => $this->reason,
            'description' => $this->description ? $this->description : '',
            'return_status' => $this->return_status,
            'images' => $this->returnOrderImages ? $this->returnOrderImages->map(function ($image) {
                return [
                    'image_name' => $image->image_name,
                    'image_path' => asset('storage/' . $image->image_path),
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ];
            }) : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
