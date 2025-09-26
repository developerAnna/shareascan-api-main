<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnOrderImage extends Model
{
    protected $fillable = ['return_orders_id', 'image_name', 'image_path'];
}
