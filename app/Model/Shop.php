<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    // use SoftDeletes;
    protected $fillable = [
        'user_id', 'status', 'online', 'shop_name', 'shop_tagline', 'shop_logo', 'shop_mobile', 'shop_whatsapp', 'shop_email', 'shop_location', 'shop_delivery_radius', 'shop_latitude', 'shop_longitude', 'shop_address'
    ];

    protected $appends = ['shop_logo_path', 'is_wishlist'];

    public function products()
    {
        return $this->hasMany('App\Model\Product');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getShopAddressAttribute($value)
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
            ];
        }
    }

    public function getShopLogoPathAttribute()
    {
        if ($this->shop_logo != null) {
            return asset('uploads/shop/' . $this->shop_logo);
        } else {
            return asset('images/shop-noimg.png');
        }
    }

    public function getIsWishlistAttribute()
    {
        if (\Auth::guard('api')->check()) {
            return (bool) \App\Model\ShopWishlist::where('user_id', \Auth::guard('api')->id())->where('shop_id', $this->id)->exists();
        } else {
            return null;
        }
    }
}
