<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'shop_id', 'type', 'code', 'cust_name', 'cust_mobile', 'cust_latitude', 'cust_longitude', 'cust_location', 'cust_address', 'item_total', 'delivery_charge', 'wallet_deducted', 'coupon_discount', 'payable_amount', 'wallet_cashback', 'admin_charge', 'status', 'expected_delivery', 'expected_intransit', 'coupon_id', 'payment_mode', 'payment_txnid', 'payment_refid', 'deliveryboy_id', 'deliveryboy_status', 'deliveryboy_reachedstore', 'shop_rating', 'shop_review', 'deliveryboy_rating', 'deliveryboy_review', 'status_log', 'user_cancel_reason' , 'merchant_total'
    ];

    protected $appends = ['invoice'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->generate_status_log();
        });

        self::created(function ($model) {
            $model->update_status_log();
        });
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo('App\Model\Shop');
    }

    public function order_products()
    {
        return $this->hasMany('App\Model\OrderProduct', 'order_id', 'id')->with('product');
    }

    public function return_replacements()
    {
        return $this->hasMany('App\Model\OrderReturnReplace', 'order_id', 'id');
    }

    public function deliveryboy_logs()
    {
        return $this->hasMany('App\Model\OrderDeliveryboyLog', 'order_id', 'id');
    }

    public function deliveryboy()
    {
        return $this->belongsTo('App\User', 'deliveryboy_id', 'id');
    }

    public function getCustAddressAttribute($value)
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

    public function getStatusLogAttribute($value)
    {
        return json_decode($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function generate_status_log()
    {
        if ($this->payment_mode == "cash") {
            $array = [
                'received' => config('orderstatus.options')['received'],
                'accepted' => config('orderstatus.options')['accepted'],
                'processed' => config('orderstatus.options')['processed'],
                'intransit' => config('orderstatus.options')['intransit'],
                'outfordelivery' => config('orderstatus.options')['outfordelivery'],
                'delivered' => config('orderstatus.options')['delivered'],
                // 'cancelled' => config('orderstatus.options')['cancelled'],
                // 'returned' => onfig(c'orderstatus.options')['returned'],
            ];
        } else {
            $array = [
                // 'paymentinitiated' => 'Payment Initiated',
                // 'paymentfailed' => 'Payment Failed',
                'received' => config('orderstatus.options')['received'],
                'accepted' => config('orderstatus.options')['accepted'],
                'processed' => config('orderstatus.options')['processed'],
                'intransit' => config('orderstatus.options')['intransit'],
                'outfordelivery' => config('orderstatus.options')['outfordelivery'],
                'delivered' => config('orderstatus.options')['delivered'],
                // 'cancelled' => config('orderstatus.options')['cancelled'],
                // 'returned' => onfig(c'orderstatus.options')['returned'],
            ];
        }

        $status_log = array();
        foreach ($array as $key => $value) {
            $status_log[] = (object)[
                'key' => $key,
                'label' => $value,
                'timestamp' => null,
            ];
        }

        return $this->status_log = json_encode($status_log);
    }

    public function update_status_log()
    {
        return \Myhelper::updateOrderStatusLog($this->id);
    }

    public function getInvoiceAttribute()
    {
        if (in_array($this->status, ['delivered'])) {
            return route('invoice.order', ['type' => 'order', 'id' => encrypt($this->id)]);
        }
    }
}
