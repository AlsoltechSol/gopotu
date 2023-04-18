<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    // use SoftDeletes;
    protected $fillable = ['master_id', 'image'];
    protected $appends = ['image_path'];

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
        return asset('uploads/product/gallery/' . $this->image);
    }
}
