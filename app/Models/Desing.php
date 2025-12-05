<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desing extends Model
{
    protected $fillable = [
        'image_name',
        'image_path',
        'x_axis',
        'y_axis',
        'target_width',
        'target_height',
        'rotation'
    ];
}
