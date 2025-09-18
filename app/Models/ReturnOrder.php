<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
    protected $fillable = ['order_id', 'reason', 'description', 'is_send_to_merchmake', 'return_status', 'cancle_reason'];

    public function returnOrderImages()
    {
        return $this->hasMany(ReturnOrderImage::class, 'return_orders_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
