<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'question',
        'options',
        'expires_at',
    ];

    protected $casts = [
        'options' => 'array',
        'expires_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}