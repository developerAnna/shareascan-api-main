<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
        'order_id',
        'address_type',
        'first_name',
        'last_name',
        'address_1',
        'address_2',
        'city',
        'state',
        'phone',
        'country_code',
        'zip',
        'email',
    ];
}
