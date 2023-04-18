<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AppBanner extends Model
{
    protected $fillable = ['position', 'type', 'image', 'status'];
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
        return asset('uploads/appbanner/' . $this->image);
    }
}
