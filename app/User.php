<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'role_id', 'email', 'password', 'mobile', 'profile_image', 'referral_code', 'parent_id', 'scheme_id', 'business_category', 'branchwallet', 'riderwallet', 'creditwallet', 'vaccination', 'latitude', 'longitude', 'online', 'status', 'email_verified_at', 'mobile_verified_at', 'resetpwd', 'alloted_shops'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
    ];

    protected $with = ['role'];
    protected $appends = ['avatar'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo('App\Model\Role');
    }

    public function documents()
    {
        return $this->belongsTo('App\Model\UserDocument', 'id', 'user_id');
    }

    public function bankdetails()
    {
        return $this->belongsTo('App\Model\UserBankDetail', 'id', 'user_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M Y', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M Y - h:i A', strtotime($value));
    }

    public function getAvatarAttribute()
    {
        if ($this->profile_image == null) {
            return 'https://via.placeholder.com/160/C5E3F9/669ACD?text=' . strtoupper(substr($this->name, 0, 1)[0]);
        } else {
            return asset('uploads/profile/' . $this->profile_image);
        }
    }
}
