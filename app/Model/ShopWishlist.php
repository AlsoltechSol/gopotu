<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopWishlist extends Model
{
    // use SoftDeletes;
    protected $fillable = ['user_id', 'shop_id'];

    public function shop(){
        return $this->belongsTo('App\Model\Shop', 'shop_id', 'id');
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
