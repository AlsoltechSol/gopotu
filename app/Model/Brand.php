<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'icon', 'status'];
    protected $appends = ['icon_path'];

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getIconPathAttribute()
    {
        if ($this->icon)
            return asset('uploads/brand/' . $this->icon);

        return asset('inhouse/dist/img/no-img.jpg');
    }
}
