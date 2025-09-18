<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderItemQrCodes;
use App\Models\OrderItems;
use App\Services\MerchMake;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        if ($request->isMethod('post')) {

            return [
                'id' => $this->id,
                'user_id' => $this->user_id,
                'order_total' => $this->total,
                'order_items' => $this->orderItems->groupBy('cart_id')->map(function ($items) {
                    return [
                        'id' => $items->first()->id,
                        'product_id' => $items->first()->product_id,
                        'product_title' => $items->first()->product_title,
                        'product_variation_id' => $items->first()->product_variation_id,
                        'variation_color' => $items->first()->variation_color,
                        'variation_size' => $items->first()->variation_size,
                        'qty' => $items->sum('qty'),
                        'price' => $items->first()->price,
                    ];
                })->values(),
                'shipping_address' => $this->shippingAddress,
                'note' => $this->note,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        } else {

            return [
                'id' => $this->id,
                'merchmake_order_id' => $this->merchmake_order_id,
                'merchmake_order_status' => $this->merchmake_order_status,
                'user_id' => $this->user_id,
                // 'order_total' => $this->orderItems->sum('total'),
                'order_items' => $this->orderItems->groupBy('cart_id')->map(function ($items) {
                    $product_image = getProductImage($items->first()->product_id, $items->first()->product_variation_id);
                    // Ensure the group contains order items
                    return [
                        'order_item_id' => $items->first()->id,
                        'product_id' => $items->first()->product_id,
                        'product_title' => $items->first()->product_title,
                        'product_variation_id' => $items->first()->product_variation_id,
                        'variation_color' => $items->first()->variation_color,
                        'variation_size' => $items->first()->variation_size,
                        'qty' => $items->sum('qty'),
                        'price' => $items->first()->price,
                        'total' => $items->sum('price'),
                        'product_images' => $product_image,
                        'qr_codes' => $items->flatMap(function ($item) {
                            // Loop through each item to get QR codes for each order item
                            return $item->getOrderItemQrCodes->map(function ($code) {
                                return [
                                    'id' => $code->id,
                                    'position' => $code->position,
                                    'qr_image' => $code->qr_image,
                                    'qr_image_url' => asset('storage/' . $code->qr_image_path),
                                    'status' => $code->status,
                                    'qr_content' => $code->qr_content,
                                    'qrcode_content_type' => $code->qrcode_content_type,
                                ];
                            });
                        })->values(),
                    ];
                })->values(),
                'shipping_address' => $this->shippingAddress,
                'has_return_request' => isset($this->ordeRefund) && !empty($this->ordeRefund) ? "yes" : "no",
                'return_order_status' => isset($this->ordeRefund) && !empty($this->ordeRefund) ? $this->ordeRefund->return_status : "",
                'note' => $this->note,
                'order_status' => $this->order_status,
                'payment_status' => $this->payment_status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }
    }
}
