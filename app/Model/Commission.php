<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['scheme_id', 'provider_id', 'type', 'value', 'product'];

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function scheme(){
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }


}
