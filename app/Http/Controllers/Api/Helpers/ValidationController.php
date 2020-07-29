<?php

namespace App\Http\Controllers\Api\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Mail\SendEmails;
use Illuminate\Support\Facades\Mail;

class ValidationController extends Controller
{
    # Verify email first time
    public function validateAcount()
    {
        $password = request('password_code');
        $email = request('email_code');
        $verify_email = 0;

        $user_email = User::where('code_email_verify', $email)
        ->first();

        $user_password = User::where('code_password_verify', $password)
        ->first();

        if(!$user_email && !$user_password){
            return response()->json(['response' => ['error' => ['Error']]], 404);
        }

        if($user_email->email_verify == 0){
            $user_email->email_verify = 1;
            $user_email->email_verified_at = date('Y-m-d H:i:s');
            $user_email->update();

            $verify_email = 1;
        }

        if($user_password->password_verify == 1){
            return response()->json(['response' => ['error' => ['La cuenta ya se encuentra activada y la contraseña ya se ha actualizado.']]], 400);
        }

        if($verify_email){
            return response()->json(['response' => 'Cuenta Verificada', 'data' => $verify_email], 200);
        }else{
            return response()->json(['response' => 'La cuenta ya se encuentra verificada.', 'data' => $verify_email], 200);
        }

    }

    # First time password update
    public function updateFirstPassword(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'password_code' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }
        $password = request('password_code');

        $user_password = User::where('code_password_verify', $password)
        ->first();

        if(!$user_password){
            return response()->json(['response' => ['error' => ['Error']]], 404);
        }

        if($user_password->password_verify){
            return response()->json(['response' => ['error' => ['La contraseña ya ha sido cambiada.']]], 400);
        }

        $user_password->password = bcrypt(request('password'));
        $user_password->password_verify = 1;
        $user_password->update();

        return response()->json(['response' => 'Contraseña actualizada'], 200);


    }

    # Validate account when changing mail
    public function validateNewEmail(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'email_code' => 'required',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }
        $email = request('email_code');
        $verify_email = 0;

        $user_email = User::where('code_email_verify', $email)
        ->first();

        if(!$user_email){
            return response()->json(['response' => ['error' => ['Error']]], 404);
        }

        if($user_email->email_verify == 0){
            $user_email->email_verify = 1;
            $user_email->update();

            $verify_email = 1;
        }

        if($verify_email){
            return response()->json(['response' => 'Cuenta Verificada', 'data' => $verify_email], 200);
        }else{
            return response()->json(['response' => 'La cuenta ya se encuentra verificada.', 'data' => $verify_email], 200);
        }

    }

    # Change of password
    public function newPassword(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'password' => 'required|min:6',
            'new_password' => 'required|confirmed|min:6',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $user = User::find(Auth::id());

        if(!$user){
            return response()->json(['response' => ['error' => ['Usuario no encontrado']]], 404);
        }

        if (Hash::check(request('password'), $user->password)) {

            if(request('password') == request('new_password')){
                return response()->json(['response' => ['error' => ['Por favor insertar una contraseña diferente a la anterior.']]], 400);
            }

            $user->password = bcrypt(request('new_password'));
            $user->password_verify = 1;
            $user->update();
        }else{
            return response()->json(['response' => ['error' => ['Contraseña incorrecta']]], 400);
        }

        return response()->json(['response' => 'Contraseña actualizada'], 200);


    }

    # Send email when the password is forget
    public function forgetPassword(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'email' => 'required|email',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $user = User::where('email', request('email'))->first();
        if(!$user){
            return response()->json(['response' => ['error' => ['Usuario no encontrado']]], 400);
        }

        # Here we will generate a code to verify the email
        while(TRUE){
            # Here we create a code
            $password_code = md5(uniqid(rand(), true));
            # Here we check if there is a User that has the same email verification code
            $code_password_exist = User::where('code_password_verify', $password_code)->first();
            # If there is not, we exit the loop
            if (!$code_password_exist){
                break;
            }
        }


        $user->code_password_verify = $password_code;
        $user->password_verify = 0;
        $user->update();

        /*$data = array(
            'password_code' => $password_code,
            'name' => $user->name." ".$user->last_name,
            'email' => $user->email,
        );

        $principal_email = array((object)['email' => $user->email, 'name' => $user->name." ".$user->last_name]);

        $send_email = SendEmailHelper::sendEmail('Cambio de contraseña.', TemplatesHelper::forgetPassword($data), $principal_email, array());
        if($send_email != 1){
            return response()->json(['response' => ['error' => [$send_email]]], 400);
        }*/

        # We generate the data to send the mail to the created user
        $data = array(
            'password_code' => $password_code,
            'name' => $user->name." ".$user->last_name,
            'email' => $user->email,
        );

        # return response()->json(['response' => ['error' => ['Dile a ronaldo que te cree un pqrs, con un correo tuyo, para que no le llegue el correo a un cliente de verdad.']]], 400);
        # Send Notification
        $mail = Mail::to($user->email)->send(new SendEmails('forget_password', 'Cambio de contraseña.', 'correo', $data));

        if($mail){
            return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
        }

        return response()->json(['response' => 'Se a enviado un correo con la solicitud de cambio de contraseña.'], 200);

    }
}
