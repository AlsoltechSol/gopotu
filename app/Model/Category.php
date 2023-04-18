<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = ['parent_id', 'name', 'type', 'icon', 'status', 'scheme_id'];
    protected $appends = ['icon_path'];

    public function parent_category()
    {
        return $this->belongsTo('App\Model\Category', 'parent_id', 'id');
    }

    public function sub_categories()
    {
        return $this->hasMany('App\Model\Category', 'parent_id', 'id')->with('sub_categories');
    }

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
        return asset('uploads/category/' . $this->icon);
    }

    public function scheme(){
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}

