<?php

namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'slug', 'type', 'role_id'];

    protected $appends = ['roles'];

    public function getRolesAttribute(){
        $roles = Role::whereIn('id', json_decode($this->role_id))->pluck('name')->toArray();
        return implode(', ', $roles);
    }

	public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
}
