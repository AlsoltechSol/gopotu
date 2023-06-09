<?php

namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scheme extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'status'];

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function categories(){
        return $this->hasMany(Category::class, 'scheme_id');
    }

    public function productMasters(){
        return $this->hasMany(ProductMaster::class, 'scheme_id');
    }

    public function commissions(){
        return $this->hasMany(Commission::class, 'scheme_id');
    }
}
