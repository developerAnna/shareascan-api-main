<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomePageCategoryResource extends JsonResource
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
            'title' => get_category_title($this->title),
            'description' => $this->description,
            'image_name' => $this->image ?? '',
            'image_path' => $this->image ?  asset('CategoryImages/' . $this->image) : '',
            'created_at' =>  $this->created_at,
            'updated_at' =>  $this->updated_at,

        ];
    }
}
