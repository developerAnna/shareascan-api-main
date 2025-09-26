<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemQrCodes extends Model
{
    protected $fillable = ['order_item_id', 'status', 'position', 'qr_image', 'qr_image_path', 'hex_color', 'qr_content', 'qrcode_content_type'];
}
