<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderReturnReplace extends Model
{
    protected $fillable = [
        'order_id', 'code', 'status', 'type', 'status_log', 'delivery_charge', 'deliveryboy_id', 'deliveryboy_status', 'deliveryboy_reachedstore', 'expected_intransit', 'expected_delivery', 'adminremarks'
    ];

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

    public function order()
    {
        return $this->belongsTo('App\Model\Order');
    }

    public function deliveryboy()
    {
        return $this->belongsTo('App\User', 'deliveryboy_id', 'id');
    }

    public function deliveryboy_logs()
    {
        return $this->hasMany('App\Model\OrderReturnReplaceDeliveryboyLog', 'returnreplace_id', 'id');
    }

    public function returnreplacement_items()
    {
        return $this->hasMany('App\Model\OrderReturnReplaceItem', 'returnreplace_id', 'id')->with('orderproduct');
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
        $array = [];
        if ($this->type == "return") {
            $array = [
                'initiated' => config('returnreplacestatus.options')['initiated'],
                'accepted' => config('returnreplacestatus.options')['accepted'],
                'outforpickup' => config('returnreplacestatus.options')['outforpickup'],
                'outforstore' => config('returnreplacestatus.options')['outforstore'],
                'deliveredtostore' => config('returnreplacestatus.options')['deliveredtostore'],
                // 'rejected' => config('returnreplacestatus.options')['rejected'],
            ];
        } elseif ($this->type == "replace") {
            $array = [
                'initiated' => config('returnreplacestatus.options')['initiated'],
                'accepted' => config('returnreplacestatus.options')['accepted'],
                'processed' => config('returnreplacestatus.options')['processed'],
                'intransit' => config('returnreplacestatus.options')['intransit'],
                'outfordelivery' => config('returnreplacestatus.options')['outfordelivery'],
                'delivered' => config('returnreplacestatus.options')['delivered'],
                'outforstore' => config('returnreplacestatus.options')['outforstore'],
                'deliveredtostore' => config('returnreplacestatus.options')['deliveredtostore'],
                // 'rejected' => config('returnreplacestatus.options')['rejected'],
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
        return \Myhelper::updateOrderReturnReplaceStatusLog($this->id);
    }
}
