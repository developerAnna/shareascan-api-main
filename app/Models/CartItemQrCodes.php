<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemQrCodes extends Model
{
    protected $fillable = ['cart_id', 'qrcode_id', 'position'];

    public function qrcode()
    {
        return $this->belongsTo(Qrcodes::class, 'qrcode_id', 'id');
    }

  


}
