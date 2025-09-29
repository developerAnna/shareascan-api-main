<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReply extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'parent_reply_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function children()
    {
        return $this->hasMany(PostReply::class, 'parent_reply_id')->with(['user', 'children']);
    }



}
