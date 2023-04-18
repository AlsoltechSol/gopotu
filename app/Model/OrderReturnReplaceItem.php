<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderReturnReplaceItem extends Model
{
    protected $fillable = [
        'returnreplace_id', 'orderproduct_id'
    ];

    public function returnreplace()
    {
        return $this->belongsTo('App\Model\OrderReturnReplace');
    }

    public function orderproduct()
    {
        return $this->belongsTo('App\Model\OrderProduct')->with('product');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
}
