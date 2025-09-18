<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreWebhook extends Model
{
    protected $fillable = ['order_id', 'hoook_id', 'hook_type', 'hook_status', 'event_id', 'hook_data', 'payment_method'];
}
