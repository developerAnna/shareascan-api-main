<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'variation_type',
        'variation_value',
        'price_adjustment',
        'stock_quantity',
        'sku_suffix',
        'image_url',
        'is_default',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFinalPriceAttribute()
    {
        return $this->product->price + $this->price_adjustment;
    }

    public function getFullSkuAttribute()
    {
        return $this->product->sku . ($this->sku_suffix ? '-' . $this->sku_suffix : '');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('variation_type', $type);
    }

    public function scopeByValue($query, $value)
    {
        return $query->where('variation_value', $value);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Methods
    public function isInStock()
    {
        return $this->stock_quantity > 0;
    }

    public function decreaseStock($quantity)
    {
        $this->decrement('stock_quantity', $quantity);
    }

    public function increaseStock($quantity)
    {
        $this->increment('stock_quantity', $quantity);
    }
}
