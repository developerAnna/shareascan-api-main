<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data =  [
            'id' => $this->id,
            'qr_content' => $this->qr_content,
            'qrcode_content_type' => $this->qrcode_content_type,
        ];

        if ($this->qrcode_content_type == "document") {
            $data['qr_content'] = asset('storage/QrcodeContentDocuments/' . $this->qr_content);
        }

        return $data;
    }
}
