<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DefaultPermission extends Model
{
    protected $fillable = ['role_id', 'permission_id'];
    public $timestamps = false;
}
