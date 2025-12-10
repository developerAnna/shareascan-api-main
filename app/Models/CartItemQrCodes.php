<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemQrCodes extends Model
{
    protected $fillable = ['cart_id', 'qrcode_id', 'position','design_id','desing_with_qr'];

    public function qrcode()
    {
        return $this->belongsTo(Qrcodes::class, 'qrcode_id', 'id');
    }

    public function design()
    {
        return $this->belongsTo(Desing::class, 'design_id', 'id');
    }




}
