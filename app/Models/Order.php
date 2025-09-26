<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{

    use SoftDeletes;

    protected $fillable = ['user_id', 'total', 'payment_method', 'payment_status', 'merchmake_order_id', 'merchmake_invoice_id', 'merchmake_order_status', 'note', 'merchmake_response', 'paypal_id', 'paypal_token','status'];


    protected $dates = ['deleted_at'];
    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'id', 'order_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'order_id', 'id');
    }

    public function ordeRefund()
    {
        return $this->hasOne(ReturnOrder::class, 'order_id', 'id');
    }
}
