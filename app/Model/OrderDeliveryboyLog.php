<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryboyLog extends Model
{
    protected $fillable = ['order_id', 'deliveryboy_id', 'status', 'description'];

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

    public function order()
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }
}
