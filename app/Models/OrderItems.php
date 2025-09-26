<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    public function getOrderItemQrCodes(){
        return $this->hasMany(OrderItemQrCodes::class, 'order_item_id', 'id');
    }
}
