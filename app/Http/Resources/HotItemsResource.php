<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product_image = getProductImage($this->product_id, null);

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'price' => getProductPrice($this->product_id),
            'product_title' => $this->title,
            'product_images' => $product_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
