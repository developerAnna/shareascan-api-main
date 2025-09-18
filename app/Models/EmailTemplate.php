<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'body', 'subject'];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = mb_strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $value)));
    }
}
