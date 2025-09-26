<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $fillable = ['user_id', 'phone_number', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'zipcode'];

    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
