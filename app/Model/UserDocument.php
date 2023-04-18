<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    protected $fillable = ['user_id', 'drivinglicense_number', 'drivinglicense_expiry', 'drivinglicense_front', 'drivinglicense_back', 'govtid_type', 'govtid_number', 'govtid_front', 'govtid_back', 'tradelicense_number', 'tradelicense_doc', 'fssaireg_number', 'fssaireg_doc', 'gstin_number', 'gstin_doc'];

    protected $appends = ['drivinglicense_front_path', 'drivinglicense_back_path', 'govtid_front_path', 'govtid_back_path', 'tradelicense_doc_path', 'fssaireg_doc_path', 'gstin_doc_path'];

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getDrivingLicenseFrontPathAttribute()
    {
        if ($this->drivinglicense_front) {
            return asset('uploads/profile/documents/' . $this->drivinglicense_front);
        } else {
            return null;
        }
    }

    public function getDrivingLicenseBackPathAttribute()
    {
        if ($this->drivinglicense_back) {
            return asset('uploads/profile/documents/' . $this->drivinglicense_back);
        } else {
            return null;
        }
    }

    public function getGovtidFrontPathAttribute()
    {
        if ($this->govtid_front) {
            return asset('uploads/profile/documents/' . $this->govtid_front);
        } else {
            return null;
        }
    }

    public function getGovtidBackPathAttribute()
    {
        if ($this->govtid_back) {
            return asset('uploads/profile/documents/' . $this->govtid_back);
        } else {
            return null;
        }
    }

    public function getTradelicenseDocPathAttribute()
    {
        if ($this->tradelicense_doc) {
            return asset('uploads/profile/documents/' . $this->tradelicense_doc);
        } else {
            return null;
        }
    }

    public function getFssairegDocPathAttribute()
    {
        if ($this->fssaireg_doc) {
            return asset('uploads/profile/documents/' . $this->fssaireg_doc);
        } else {
            return null;
        }
    }

    public function getGstinDocPathAttribute()
    {
        if ($this->gstin_doc) {
            return asset('uploads/profile/documents/' . $this->gstin_doc);
        } else {
            return null;
        }
    }
}
