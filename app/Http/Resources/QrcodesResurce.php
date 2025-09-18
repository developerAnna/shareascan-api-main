<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrcodesResurce extends JsonResource
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
            'qr_content' => $this->qr_content,
            'qrcode_content_type' => $this->qrcode_content_type,
            'qr_image' => $this->qr_image,
            'qr_image_path' => asset('storage/' . $this->qr_image_path),
            'position' => $this->position,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
