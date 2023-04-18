<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;
    protected $fillable = ['product_id', 'variant', 'color', 'price', 'offeredprice', 'quantity', 'sku'];
    protected $appends = ['purchase_price', 'cart_quantity', 'variant_name'];

    public function product()
    {
        return $this->belongsTo('App\Model\Product', 'product_id', 'id')->with('details')->withTrashed();
    }

    public function color_details()
    {
        return $this->belongsTo('App\Model\Color', 'color', 'code');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getPurchasePriceAttribute()
    {
        $value = $this->price;
        if ($this->offeredprice != null) {
            $value = $this->offeredprice;
        }

        return $value;
    }

    public function getCartQuantityAttribute()
    {
        if (\Auth::guard('api')->check()) {
            return (int) \App\Model\Cart::where('user_id', \Auth::guard('api')->id())->where('variant_id', $this->id)->sum('quantity');
        } elseif (@getallheaders()['guest-token'] || @getallheaders()['Guest-Token']) {
            if (@getallheaders()['Guest-Token'])
                $guesttoken = @getallheaders()['Guest-Token'];
            elseif (@getallheaders()['guest-token'])
                $guesttoken = @getallheaders()['guest-token'];

            $guest = \App\Guest::where('token', $guesttoken)->select('id')->first();
            if ($guest)
                return (int) \App\Model\Cart::where('guest_id', $guest->id)->where('variant_id', $this->id)->sum('quantity');
        }

        return null;
    }

    public function getVariantNameAttribute()
    {
        if ($this->variant) {
            $temp = explode(':', $this->variant);
            return @$temp[1];
        } else {
            return null;
        }
    }
}
