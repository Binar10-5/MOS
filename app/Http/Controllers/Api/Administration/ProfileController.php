<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function userProfile()
    {
        $user = User::select('users.id', 'users.name', 'users.last_name', 'users.cell_phone', 'users.dni', 'users.email')
        ->find(Auth::id());

        $roles = Role::select('role.id', 'role.name', 'role.description')
        ->join('user_has_role as ur', 'role.id', 'ur.role_id')
        ->where('ur.user_id', $user->id)
        ->get();

        foreach ($roles as $role) {
            $permissions = Permission::select('permission.id', 'permission.name', 'permission.description', 'permission.module_id')
            ->join('role_has_permission as rp', 'permission.id', 'rp.permission_id')
            ->where('rp.role_id', $role->id)
            ->get();

            $role->permissions = $permissions;
        }

        $user->roles = $roles;

        return response()->json(['response' => $user], 200);
    }
}
