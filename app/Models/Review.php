<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'product_id', 'status', 'star_count', 'content', 'product_title'];

    public function reviewImages()
    {
        return $this->hasMany(ReviewImages::class, 'review_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
