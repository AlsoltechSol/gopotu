<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderReturnReplaceDeliveryboyLog extends Model
{
    protected $fillable = ['returnreplace_id', 'deliveryboy_id', 'status', 'description'];

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function deliveryboy()
    {
        return $this->belongsTo('App\User', 'deliveryboy_id', 'id');
    }

    public function orderreturnreplace()
    {
        return $this->belongsTo('App\Model\OrderReturnReplace', 'returnreplace_id', 'id');
    }
}
