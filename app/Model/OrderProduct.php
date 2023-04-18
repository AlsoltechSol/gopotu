<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'variant_selected', 'price', 'tax', 'quantity', 'sub_total', 'tax_total', 'shop_tin'
    ];

    public function product()
    {
        return $this->belongsTo('App\Model\Product')->with('details')->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo('App\Model\Order');
    }

    public function getVariantSelectedAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        } else {
            return (object)[
                'color_code' => null,
                'color_name' => null,
                'variant_code' => null,
                'variant_name' => null,
            ];
        }
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
