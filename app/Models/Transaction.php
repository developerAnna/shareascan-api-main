<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [

        'order_id',
        'transaction_id',
        'charge_succeed_res',
        'cancle_res',
        'refund_res',
        'stripe_response',
        'payment_method',
        'amount',
        'charge_id',
        'amount',

    ];

    protected $dates = ['deleted_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
