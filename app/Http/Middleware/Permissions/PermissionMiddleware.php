<?php

namespace App\Http\Middleware\Permissions;

use App\Models\Permission;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        # Get user by id
        $user = User::find(Auth::id());

        # Validate if the user exists
        if(!$user){
            return response()->json(['response' => ['error' => ['Usuario no encontrado']]], 404);
        }

        # Validate if the user's roles have the requested permission
        $validate_permission = Permission::select('permission.id')
        ->join('role_has_permission as rp', 'permission.id', 'rp.permission_id')
        ->join('role as r', 'rp.role_id', 'r.id')
        ->join('user_has_role as ur', 'r.id', 'ur.role_id')
        ->join('users as u', 'ur.user_id', 'u.id')
        ->where('u.id', $user->id)
        ->where('permission.id', $permission)
        ->first();

        # Validate if you do not have permission
        if(!$validate_permission){
            return response()->json(['response' => ['error' => ['No tienes permiso']]], 403);
        }

        return $next($request);
    }
}
