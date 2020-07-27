<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Permission;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function modulegeneral(Request $request)
    {
        # Get user
        $user = User::find(Auth::id());

        # Validate if the user exists
        if(!$user){
            return response()->json(['response' => ['error' => ['Usuario no encontrado']]], 404);
        }

        # Get the modules with their respective permissions
        $module_list = Module::select('module.id as module_id', 'module.name as module_name', 'p.id as permission_id', 'p.name as permission_name')
        ->join('permission as p', 'module.id', 'p.module_id')
        ->join('role_has_permission as rp', 'p.id', 'rp.permission_id')
        ->join('role as r', 'rp.role_id', 'r.id')
        ->join('user_has_role as ur', 'r.id', 'ur.role_id')
        ->join('users as u', 'ur.user_id', 'u.id')
        ->distinct('u.id')
        ->get();

        # Order by the module name
        $collection = collect($module_list)->sortBy('module_name');
        # Group by the module name
        $modules = collect($collection)->groupBy('module_name');

        return response()->json(['response' => $modules], 200);
    }
}
