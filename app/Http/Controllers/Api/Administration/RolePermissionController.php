<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role_permission = RolePermission::select('p.id as permission_id', 'p.name as permission_name', 'p.description as permission_description',
        'r.id as role_id', 'r.name as role_name', 'r.description as role_description', 'p.module_id as module_id', 'm.name as module_name',
        'm.description as module_description')
        ->join('role as r', 'role_has_permission.role_id', 'r.id')
        ->join('permission as p', 'role_has_permission.permission_id', 'p.id')
        ->join('module as m', 'p.module_id', 'm.id')
        ->get();

        $collection = collect($role_permission)->sortBy('role_name');
        $group = collect($collection)->groupBy('role_name');


        return response()->json(['response' => $group], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role_permission = RolePermission::select('p.id as permission_id', 'p.name as permission_name', 'p.description as permission_description',
        'm.id as module_id', 'm.name as module_name',
        'm.description as module_description')
        ->join('role as r', 'role_has_permission.role_id', 'r.id')
        ->join('permission as p', 'role_has_permission.permission_id', 'p.id')
        ->join('module as m', 'p.module_id', 'm.id')
        ->where('r.id', $id)
        ->get();

        $collection = collect($role_permission)->sortBy('module_name');
        $group = collect($collection)->groupBy('module_name');


        return response()->json(['response' => $group], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator=\Validator::make($request->all(),[
            'add_array' => 'bail|array',
            'delete_array' => 'bail|array',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        # Here we get the instance of an role
        $role = Role::find($id);

        # Here we check if the role does not exist
        if(!$role){
            return response()->json(['response' => ['error' => ['Rol no encontrado']]], 404);
        }

        DB::beginTransaction();
        try{
            if(count(request('delete_array')) > 0){
                foreach (request('delete_array') as $delete_array) {
                    # We need to remove the permissions id for each record in the list.
                    $validate_role_permission = RolePermission::where('role_id', $role->id)->where('permission_id', $delete_array)->first();

                    if($validate_role_permission){
                        $validate_role_permission->delete();
                    }
                }
            }

            if(count(request('add_array')) > 0){
                foreach (request('add_array') as $add_array) {
                    # We need to add the permissions id for each record in the list.
                    $validate_role_permission = RolePermission::where('role_id', $role->id)->where('permission_id', $add_array)->first();

                    if(!$validate_role_permission){
                        $role_has_permission = RolePermission::create([
                            'role_id' => $role->id,
                            'permission_id' => $add_array,
                        ]);
                    }
                }
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al agregar permisos al rol'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }
        # Here we return success.
        DB::commit();
        return response()->json(['response' => 'Permisos asignados con exito.'], 200);
    }

}
