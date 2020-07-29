<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    public function __construct()
    {
        # the middleware param 4 = List roles
        $this->middleware('permission:/list_role')->only(['show', 'index']);
        # the middleware param 5 = Create, update, delete roles
        $this->middleware('permission:/create_role')->only(['store', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::get();


        foreach ($roles as $role) {
            $permissions = RolePermission::select('p.id', 'p.name', 'p.description')
            ->join('permission as p', 'role_has_permission.permission_id', 'p.id')
            ->where('role_has_permission.role_id', $role->id)
            ->get();

            $role->permissions = $permissions;
        }

        return response()->json(['response' => $roles], 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'name' => 'required|min:1|max:75|unique:role,name',
            'description' => 'required',
            'add_array' => 'bail|array',
            'add_array.*' => 'bail|exists:permission,id'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }


        DB::beginTransaction();
        try{

            # Create a role
            $role = Role::create([
                'name' => request('name'),
                'description' => request('description')
            ]);

            $valid_data = array();
            foreach (request('add_array') as $add_array) {
                # We need to add the permissions id for each record in the list.
                $validate_role_permission = RolePermission::where('role_id', $role->id)->where('permission_id', $add_array)->first();

                if(!$validate_role_permission){
                    array_push($valid_data, [
                        'role_id' => $role->id,
                        'permission_id' => $add_array,
                    ]);
                }
            }
            $role_has_permission = RolePermission::insert($valid_data);

        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al agregar permisos al rol'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }
        # Here we return success.
        DB::commit();

        return response()->json(['response' => 'Rol creado con éxito.'], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);

        if($role){
            $permissions = RolePermission::select('p.id', 'p.name', 'p.description')
            ->join('permission as p', 'role_has_permission.permission_id', 'p.id')
            ->where('role_has_permission.role_id', $role->id)
            ->get();

            $role->permissions = $permissions;

        }


        return response()->json(['response' => $role], 200);

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
            'name' => 'required|min:1|max:75|unique:role,name,'.$id,
            'description' => 'required',
            'add_array' => 'bail|array',
            'add_array.*' => 'bail|exists:permission,id',
            'delete_array' => 'bail',
            'delete_array.*' => 'bail|exists:permission,id'
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
            $role->name = request('name');
            $role->description = request('description');
            foreach (request('delete_array') as $delete_array) {

                # We need to remove the permissions id for each record in the list.
                $validate_role_permission = RolePermission::where('role_id', $role->id)->where('permission_id', $delete_array)->first();

                # We validate that the relationship of role and permission exists
                if($validate_role_permission){
                    # 4 = List role, 5 = Create/edit role, 1 = Listar usuarios, 2 = Crear usuarios, 3 = Editar usuario
                    if($delete_array == 4 || $delete_array == 5 || $delete_array == 1 || $delete_array == 2 || $delete_array == 3){

                        # We validate that at least one user has the role permissions
                        $validate_permission = RolePermission::join('user_has_role as ur', 'role_has_permission.role_id', 'ur.role_id')
                        ->where('role_has_permission.permission_id', $delete_array)
                        ->where('role_has_permission.role_id', '!=', $role->id)
                        ->first();
                        # If there is a person with permission we can remove the permission
                        if($validate_permission){
                            $validate_role_permission->delete();
                        }else{
                            return response()->json(['response' => ['error' => ['No puedes eliminar el permiso de crear o listar roles si ningun otro rol lo tiene']]], 400);
                        }
                    }else{
                        $validate_role_permission->delete();
                    }
                }
            }

        $valid_data = array();
        foreach (request('add_array') as $add_array) {
            # We need to add the permissions id for each record in the list.
            $validate_role_permission = RolePermission::where('role_id', $role->id)->where('permission_id', $add_array)->first();



            if(!$validate_role_permission){
                array_push($valid_data, [
                    'role_id' => $role->id,
                    'permission_id' => $add_array,
                ]);
            }
        }

        $role_has_permission = RolePermission::insert($valid_data);

        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al agregar permisos al rol'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }
        $role->update();
        # Here we return success.
        DB::commit();
        return response()->json(['response' => 'Rol actualizado con éxito.'], 200);
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if(!$role){
            return response()->json(['response' => ['error' => ['Rol no encontrado']]], 404);
        }

        # We validate that at least one user has the role permissions
        $validate_permission = RolePermission::whereIn('role_has_permission.permission_id', [4, 5])
        ->where('role_has_permission.role_id', '!=', $role->id)
        ->first();
        # If there is a person with permission we can remove the permission
        if($validate_permission){
            $role->delete();
        }else{
            return response()->json(['response' => ['error' => ['No puedes eliminar el rol, si los demas roles no tienen el permiso de crear roles.']]], 400);
        }

        return response()->json(['response' => 'Rol eliminado'], 200);

    }

}
