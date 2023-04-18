<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductWishlist extends Model
{
    // use SoftDeletes;
    protected $fillable = ['user_id', 'product_id'];

    public function product(){
        return $this->belongsTo('App\Model\Product', 'product_id', 'id')->with('product_variants')->withTrashed();
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
