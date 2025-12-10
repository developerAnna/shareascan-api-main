<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product_image = getProductImage($this->product_id, $this->product_variation_id);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'price' => $this->price,
            'total' => $this->total,
            'product_variation_id' => $this->product_variation_id,
            'product_title' => $this->product_title,
            'variation_color' => $this->variation_color,
            'variation_size' => $this->variation_size,
            'art_files' => $this->cartItmesQrCodes ? $this->cartItmesQrCodes->map(function ($cartQr) {

                $design = $cartQr->design;

                return [
                    'cart_id' => $cartQr->cart_id,

                    // QR CODE DATA
                    'qrcode_id' => $cartQr->qrcode_id,
                    'qrcode_image' => $cartQr->qrcode->qr_image,
                    'qrcode_image_url' => asset('storage/' . $cartQr->qrcode->qr_image_path),

                    // DESIGN DATA
                    'design_id' => $cartQr->design_id,
                    'design_image_name' => $design ? $design->image_name : null,
                    'design_image_path' => $design ? $design->image_path : null,
                    'design_image_url' => $design ? asset('storage/' . $design->image_path) : null,

                    'qr_with_desing_final_image' => $cartQr ? asset('storage/' . $cartQr->desing_with_qr) : null,

                    // BACK / FRONT POSITION
                    'position' => $cartQr->position,
                ];
            }) : [],
            'product_images' => $product_image, // Add the images field here
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
