<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WalletRequest extends Model
{
    protected $fillable = [
        "user_id", "code", "wallet_type", "amount", "remarks", "status", "adminremarks", "type", "transaction_copy"
    ];

    protected $appends = ['transaction_copy_path'];

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

    public function getTransactionCopyPathAttribute()
    {
        if ($this->transaction_copy) {
            return asset('uploads/wallet/' . $this->transaction_copy);
        } else {
            return null;
        }
    }
}
