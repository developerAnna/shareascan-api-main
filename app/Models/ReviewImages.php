<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewImages extends Model
{
    protected $fillable = ['review_id', 'filename', 'file_path'];
}
