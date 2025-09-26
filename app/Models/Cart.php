<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_id', 'product_title', 'qty', 'price', 'product_variation_id', 'variation_color', 'variation_size', 'total'];

    public function cartItmesQrCodes()
    {
        return $this->hasMany(CartItemQrCodes::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($cart) {
            $cart->cartItmesQrCodes()->delete();
        });
    }
}
