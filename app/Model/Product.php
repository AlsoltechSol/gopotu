<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'shop_id', 'master_id', 'type', 'colors', 'variant', 'variant_options',  'status', 'availability', 'scheme_id'];
    protected $appends = [];

    public function details(){
        return $this->belongsTo('App\Model\ProductMaster', 'master_id')->withTrashed()->with('category', 'brand', 'gallery_images');
    }

    public function shop()
    {
        return $this->belongsTo('App\Model\Shop');
    }

    public function product_variants()
    {
        return $this->hasMany('App\Model\ProductVariant', 'product_id', 'id');
    }

    public function getColorsAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        } else {
            return [];
        }
    }

    public function getVariantOptionsAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        } else {
            return [];
        }
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
