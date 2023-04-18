<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WalletReport extends Model
{
    protected $fillable = [
        'user_id', 'ref_id', 'wallet_type', 'balance', 'trans_type', 'amount', 'remarks', 'service'
    ];

    protected $appends = ['closing_bal'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function boot()
    {
        parent::boot();

        self::created(function ($report) {
            $user = \App\User::find($report->user_id);
            if ($user) {
                $wallet_type = $report->wallet_type;
                if ($user->role->slug == 'user') {
                    if ($report->wallet_type == 'userwallet')
                        $wallet_type = "Main Wallet";
                } else if ($user->role->slug == 'branch') {
                    if ($report->wallet_type == 'branchwallet')
                        $wallet_type = "Main Wallet";
                } else if ($user->role->slug == 'deliveryboy') {
                    if ($report->wallet_type == 'riderwallet')
                        $wallet_type = "Earning Wallet";
                    else if ($report->wallet_type == 'creditwallet')
                        $wallet_type = "Collection Wallet";
                }

                $content = "Hey " . $user->name . ", Rs. " . $report->amount . " has been " . $report->trans_type . "ed to your " . $wallet_type . ". Current balance is Rs. " . $user->{$report->wallet_type} . ". Thanks, Team GoPotu";
                \Myhelper::sms(@$user->mobile, $content);

                \Myhelper::sendNotification($user->id, "Wallet Transaction Alert", $content);
            }
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getBalanceAttribute($value)
    {
        return number_format((float)$value, 2, '.', '');
    }

    public function getAmountAttribute($value)
    {
        return number_format((float)$value, 2, '.', '');
    }

    public function getClosingBalAttribute()
    {
        $balance = $this->balance;
        switch ($this->trans_type) {
            case 'credit':
                $balance += $this->amount;
                break;

            case 'debit':
                $balance -= $this->amount;
                break;
        }

        return number_format((float)$balance, 2, '.', '');
    }
}
