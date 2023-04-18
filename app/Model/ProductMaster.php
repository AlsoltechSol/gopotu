<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMaster extends Model
{
    use SoftDeletes;
    protected $fillable = ['type', 'name', 'category_id', 'brand_id', 'description', 'tax_rate', 'image', 'status', 'scheme_id'];
    protected $appends = ['image_path'];

    public function category()
    {
        return $this->belongsTo('App\Model\Category')->withTrashed();
    }

    public function brand()
    {
        return $this->belongsTo('App\Model\Brand')->withTrashed();
    }

    public function gallery_images()
    {
        return $this->hasMany('App\Model\ProductImage', 'master_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getImagePathAttribute()
    {
        return asset('uploads/product/' . $this->image);
    }
}
