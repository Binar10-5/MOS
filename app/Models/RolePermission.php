<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_has_permission';
    protected $fillable = [
        'permission_id', 'role_id'
    ];
}
