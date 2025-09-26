<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected $productDetails;

    public function __construct($resource, $productDetails = [])
    {
        parent::__construct($resource);
        $this->productDetails = $productDetails;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_details' => $this->productDetails,
        ];
    }
}
