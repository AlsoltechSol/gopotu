<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['code', 'type', 'user_id', 'name', 'mobile', 'alternate_mobile', 'email', 'subject', 'order_code', 'message', 'status'];

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
}
