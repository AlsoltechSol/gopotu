<?php

namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SociallinkMaster extends Model
{
    use SoftDeletes;
    protected $fillable = ['tittle', 'link', 'icon', 'status'];
    protected $appends = ['avatar'];

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getAvatarAttribute()
    {
        if ($this->icon == null) {
            return 'https://i.pinimg.com/originals/51/f6/fb/51f6fb256629fc755b8870c801092942.png';
        } else {
            return asset('uploads/sociallinks/' . $this->icon);
        }
    }
}
