<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Mail\Helpers\SendEmails;
use App\Models\Role;
use App\Models\UserRole;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function __construct()
    {
        # the middleware param 1 = List user
        $this->middleware('permission:/list_user')->only(['show', 'index']);
        # the middleware param 2 = Create user
        $this->middleware('permission:/create_user')->only('store');
        # the middleware param 3 = Update user
        $this->middleware('permission:/update_user')->only(['update', 'destroy']);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       # Get users
       if(request('paginate')){
            $users = User::name(request('name'))
            ->state(request('state'))
            ->dni(request('dni'))
            ->range(request('date_start'), request('date_end'))
            ->byname(request('order_name'))
            ->bydni(request('order_dni'))
            ->bycreatedAt(request('order_created_at'))
            ->where('id', '!=', Auth::id())
            ->paginate(8);
        }else{
            $users = User::name(request('name'))
            ->state(request('state'))
            ->dni(request('dni'))
            ->range(request('date_start'), request('date_end'))
            ->byname(request('order_name'))
            ->bydni(request('order_dni'))
            ->bycreatedAt(request('order_created_at'))
            ->where('id', '!=', Auth::id())
            ->get();
        }

        foreach ($users as $user) {


            # Get user roles
            $roles = Role::select('role.id', 'role.name', 'role.description')
            ->join('user_has_role as ur', 'role.id', 'ur.role_id')
            ->where('ur.user_id', $user->id)
            ->get();

            # Assign roles to json
            $user->roles = $roles;

        }

        return response()->json(['response' => $users], 200);

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
            'name' => 'required|max:50',
            'last_name' => 'required|max:75',
            'cell_phone' => 'required|max:15',
            'dni' => 'required|max:15|unique:users,dni',
            'email' => 'required|max:80|email|unique:users',
            'add_array' => 'bail|required|array',
            'add_array.*' => 'bail|exists:role,id'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }
        DB::beginTransaction();
        try{

            # Here we will generate a code to verify the email
            while(TRUE){
                # Here we create a code
                $email_code = md5(uniqid(rand(), true));
                $password_code = md5(uniqid(rand(), true));
                # Here we check if there is a User that has the same email verification code
                $code_email_exist = User::where('code_email_verify', $email_code)->first();
                $code_password_exist = User::where('code_password_verify', $password_code)->first();
                # If there is not, we exit the loop
                if (!$code_email_exist && !$code_password_exist){
                    break;
                }
            }

            # Create user
            $user = User::create([
                'name' => request('name'),
                'last_name' => request('last_name'),
                'cell_phone' => request('cell_phone'),
                'dni' => request('dni'),
                'email' => request('email'),
                'code_email_verify' => $email_code,
                'email_verify' => 0,
                'code_password_verify' => $password_code,
                'password_verify' => 0,
                'state_id' => 1
            ]);
            # Validate if the user was created
            if($user){

                foreach (request('add_array') as $add_array) {
                    # We need to add the role´s id for each record in the list.
                    $validate_user_has_role = UserRole::where('user_id', $user->id)->where('role_id', $add_array)->first();

                    if(!$validate_user_has_role){
                        $user_has_role = UserRole::create([
                            'user_id' => $user->id,
                            'role_id' => $add_array,
                        ]);
                    }
                }

            }else{
                return response()->json(['response' => ['error' => ['Ususario no encontrado']]], 404);
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al asignar rol'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }
        # We generate the data to send the mail to the created user
        $data = array(
            'password_code' => $password_code,
            'email_code' => $email_code,
            'name' => $user->name." ".$user->last_name,
            'email' => $user->email,
        );

        # Send Notification
        $mail = Mail::to($user->email)->send(new SendEmails('email_verify', 'Correo de verificación de cuenta.', 'noreply@mosbeautyshop.com', $data));

        if($mail){
            return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
        }

        DB::commit();
        return response()->json(['response' => 'Success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

         # Get the user by id
         $user = User::where('state_id', 1)->find($id);

         # Validate if the user exists
         if(!$user){
             return response()->json(['response' => ['error' => ['Ususario no encontrado']]], 404);
         }

         # Get user roles
         $roles = Role::select('role.id', 'role.name', 'role.description')
         ->join('user_has_role as ur', 'role.id', 'ur.role_id')
         ->where('ur.user_id', $user->id)
         ->get();

         # Assign roles to json
         $user->roles = $roles;

         return response()->json(['response' => $user], 200);
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
            'name' => 'bail|required|min:2|max:50',
            'last_name' => 'bail|required|min:2|max:75',
            'dni' => 'required|max:15|unique:users,dni,'.$id,
            'cell_phone' => 'required|max:15',
            'email' => 'bail|required|min:5|max:75|unique:users,email,'.$id,
            'state_id' => 'required|integer|exists:user_state,id',
            'add_array' => 'bail|array',
            'delete_array' => 'bail|array'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $update_email = 0;
        # Here we get the instance of an user
        $user = User::where('id', '!=', Auth::id())->find($id);

        # Here we check if the user does not exist
        if(!$user){
            return response()->json(['response' => ['error' => ['Ususario no encontrado']]], 404);
        }

        # Here we update the basic user data
        $user->name = request('name');
        $user->last_name = request('last_name');
        $user->dni = request('dni');
        $user->cell_phone = request('cell_phone');
        $user->state_id = request('state_id');

        if($user->email != request('email')){
            $user->email = request('email');
            $user->email_verify = 0;

            # Here we will generate a code to verify the email
            while(TRUE){
                # Here we create a code
                $email_code = md5(uniqid(rand(), true));
                # Here we check if there is a User that has the same email verification code
                $code_email_exist = User::where('code_email_verify', $email_code)->first();
                # If there is not, we exit the loop
                if (!$code_email_exist){
                    break;
                }
            }
            $user->code_email_verify = $email_code;

            $update_email = 1;
        }
        DB::beginTransaction();
        try{
            if(count(request('delete_array')) > 0){
                foreach (request('delete_array') as $delete_array) {
                    # We need to remove the role´s id for each record in the list.
                    $validate_user_has_role = UserRole::where('user_id', $user->id)->where('role_id', $delete_array)->first();

                    # We validate that the relationship of role and user exists
                    if($validate_user_has_role){

                        # We validate that at least one user has the role permissions
                        $validate_permission = RolePermission::join('user_has_role as ur', 'role_has_permission.role_id', 'ur.role_id')
                        ->where('role_has_permission.role_id', $delete_array)
                        ->whereIn('role_has_permission.permission_id', [4, 5, 1, 2, 3])
                        ->where('ur.user_id', '!=', $user->id)
                        ->first();
                        # If there is a person with permission we can remove the permission
                        if($validate_permission){
                            $validate_user_has_role->delete();
                        }else{
                            return response()->json(['response' => ['error' => ['No puedes eliminar el rol al usuario si los demas roles no tienen el permiso de crear roles o usuarios.']]], 400);
                        }
                    }
                }
            }

            if(count(request('add_array')) > 0){
                foreach (request('add_array') as $add_array) {
                    # We need to add the role´s id for each record in the list.
                    $validate_user_has_role = UserRole::where('user_id', $user->id)->where('role_id', $add_array)->first();

                    if(!$validate_user_has_role){
                        $user_has_role = UserRole::create([
                            'user_id' => $user->id,
                            'role_id' => $add_array,
                        ]);
                    }
                }
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al asignar rol'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }


        $user->update();
        if($update_email){
            if($user){

                # We generate the data to send the mail to the created user
                $data = array(
                    'email_code' => $email_code,
                    'name' => $user->name." ".$user->last_name,
                    'email' => $user->email,
                );

                # Send Notification
                $mail = Mail::to($user->email)->send(new SendEmails('change_email', 'Verificación de nuevo correo.', 'noreply@mosbeautyshop.com', $data));

                if($mail){
                    return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
                }
            }
        }
        # Here we return success.
        DB::commit();
        return response()->json(['response' => 'Usuario actualizado con exito.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', '!=', Auth::id())->find($id);

        if(!$user){
            return response()->json(['response' => ['error' => ['Ususario no encontrado']]], 404);
        }
        if($user->state_id == 1){
            $user->state_id = 2;
        }else if($user->state_id == 2) {
            $user->state_id = 1;
        }
        $user->update();

        return response()->json(['response' => 'Success'], 200);
    }
}
