<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function __construct()
    {
        # the middleware param 4 = List roles
        $this->middleware('permission:4')->only(['show', 'index']);
        # the middleware param 5 = Create, update, delete roles
        $this->middleware('permission:5')->only(['store', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        # Get roles
        $roles = Role::get();

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
            'name' => 'required|min:1|max:75',
            'description' => 'required',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        # Create a role
        $role = Role::create([
            'name' => request('name'),
            'description' => request('description')
        ]);

        return response()->json(['response' => 'Rol creado con exito.'], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        # Get role by id
        $rol = Role::find($id);

        return response()->json(['response' => $rol], 200);
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
            'name' => 'required|min:1|max:75',
            'description' => 'required',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        # Get role
        $role = Role::find($id);

        # Validate if the role exists
        if(!$role){
            return response()->json(['response' => ['error' => ['Rol no encontrado.']]], 404);
        }

        # Set the data to update
        $role->name = request('name');
        $role->description = request('description');

        # Update
        $role->update();

        return response()->json(['response' => 'Rol creado con exito.'], 200);
    }

}
