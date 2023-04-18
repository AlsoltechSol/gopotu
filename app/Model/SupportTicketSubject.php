<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SupportTicketSubject extends Model
{
    protected $fillable = ['type', 'name'];

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
}
