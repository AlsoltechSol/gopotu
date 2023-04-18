<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'code', 'rewarded', 'description', 'type', 'value', 'max_discount', 'min_order', 'max_usages', 'valid_till', 'status', 'applied_for_users'
    ];

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function coupon_used()
    {
        return $this->belongsTo('App\Model\Order', 'id', 'coupon_id')->whereIn('status', ['paymentinitiated', 'received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered', 'returned']);
    }

    public function getAppliedForUsersAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        } else {
            return [];
        }
    }
}
