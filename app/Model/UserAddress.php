<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    // use SoftDeletes;
    protected $fillable = [
        'user_id', 'guest_id', 'type', 'name', 'mobile', 'alternative_mobile', 'location', 'latitude', 'longitude', 'full_address', 'is_default'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function guest()
    {
        return $this->belongsTo('App\Guest');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getFullAddressAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        } else {
            return (object)[
                'address_line1' => null,
                'address_line2' => null,
                'postal_code' => null,
                'city' => null,
                'state' => null,
                'country' => null,
                'landmark' => null,
            ];
        }
    }
}
