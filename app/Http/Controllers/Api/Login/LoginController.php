<?php

namespace App\Http\Controllers\Api\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;

class LoginController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
    }

    public function login(Request $request)
    {

        $validator=\Validator::make($request->all(),[
            'username' => 'required|email',
            'password' => 'required|min:6',

            ]);

            if($validator->fails())
            {
                return response()->json(['response' => ['error' => $errors=$validator->errors()->all()]], 400);
            }
            else
            {
          //validar que el usuario exista
          $user=DB::table('users as u')->where('u.email',request('username'))->where('state_id', 1)
          ->first();
          if($user != null){
                //hashear la contraseña y validar
                if (Hash::check(request('password'), $user->password)) {
                    DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();

                    //agregar parametros al request
                    $params = [
                        'grant_type' => 'password',
                        'client_id' => $this->client->id,
                        'client_secret' => $this->client->secret,
                        'username' => request('username'),
                        'password' => request('password'),
                        'scope' => '*'
                    ];
                    //agregar parametros al request
                    $request->request->add($params);
                    $proxy = Request::create('oauth/token', 'POST');

                    return Route::dispatch($proxy);
            }else{
                return response()->json(['response' => ['error' => ['Usuario o contraseña incorrectas']]], 400);
            }
        }else{
            return response()->json(['response' => ['error' => ['Usuario no encontrado']]], 400);
          }


        }

    }


    public function refresh(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required'
        ]);

        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'username' => request('username'),
            'password' => request('password')
        ];

        $request->request->add($params);

         $proxy = Request::create('oauth/token', 'POST');

        return Route::dispatch($proxy);
    }


    public function logout(Request $request)
    {
      $access_token = Auth::user()->token();

        DB::table('oauth_refresh_tokens')
        ->where('access_token_id', $access_token->id)
        ->update(['revoked' => true]);

        $access_token->revoke();

      DB::table('oauth_access_tokens')->where('user_id', Auth::id())->delete();

      return response()->json(['message' => 'La sesion a sido cerrada con exito'], 200);


    }
}
