<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qrcodes extends Model
{
    protected $fillable = ['hexa_color', 'rgb_color', 'qr_data', 'qr_image', 'qr_image_path'];
}
