<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = ['email', 'mobile', 'otp', 'type', 'token', 'data'];

    public function setOtpAttribute($value)
    {
        $this->attributes['otp'] = bcrypt($value);
        // if (strlen($value) == 4) {
        //     $this->attributes['otp'] = bcrypt('1111');
        // } else {
        //     $this->attributes['otp'] = bcrypt('111111');
        // }
    }
}
