<?php

namespace App\Http\Controllers\Api\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateHelper extends Controller
{
    # Data to send to the account verification email template
    public static function emailVerify($data)
    {
        return view('email_verify')->with([
            'password_code' => $data['password_code'],
            'email_code' => $data['email_code'],
            'name' => $data['name'],
            'email' => $data['email']
            ]);
    }

    # Data to send to the mail exchange template
    public static function changeEmail($data)
    {
        return view('change_email')->with([
            'email_code' => $data['email_code'],
            'name' => $data['name'],
            'email' => $data['email']
            ]);
    }

    # Data to send to the password forget template
    public static function forgetPassword($data)
    {
        return view('forget_password')->with([
            'password_code' => $data['password_code'],
            'name' => $data['name'],
            'email' => $data['email']
            ]);
    }
    # Data to send to the pqrs information template
    public static function pqrsData($data)
    {
        return view('pqrs_data')->with([
            'name' => $data['name'],
            'email' => $data['email'],
            'pqrs' => $data['pqrs'],
            'message' => $data['message'],
            'pqrs_id' => $data['pqrs_id']
            ]);
    }

    # Data to send to the pqrs information template to admin
    public static function pqrsDataAdmin($data)
    {
        return view('pqrs_data_admin')->with([
            'admin_name' => $data['admin_name'],
            'name' => $data['name'],
            'email' => $data['email'],
            'cell_phone' => $data['cell_phone'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'pqrs' => $data['pqrs'],
            'pqrs_id' => $data['pqrs_id'],
            'message' => $data['message']
            ]);
    }
}
