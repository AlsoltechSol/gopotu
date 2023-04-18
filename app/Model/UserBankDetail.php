<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserBankDetail extends Model
{
    protected $fillable = ['user_id', 'accno', 'ifsccode', 'accholder', 'bankname', 'pancard_no', 'pancard_file'];
    protected $appends = ['pancard_file_path'];

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getPancardFilePathAttribute()
    {
        if ($this->pancard_file) {
            return asset('uploads/profile/bankdetails/' . $this->pancard_file);
        } else {
            return null;
        }
    }
}
