<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeVariant extends Model
{
    use SoftDeletes;
    protected $fillable = ['attribute_id', 'name', 'status'];

    public function attribute(){
        return $this->belongsTo('App\Model\ProductAttribute', 'attribute_id')->withTrashed();
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
