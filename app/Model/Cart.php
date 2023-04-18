<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    // use SoftDeletes;
    protected $fillable = ['user_id', 'guest_id', 'variant_id', 'quantity', 'shop_id', 'type'];

    public function variant(){
        return $this->belongsTo('App\Model\ProductVariant', 'variant_id', 'id')->with('product','color_details')->withTrashed();
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
