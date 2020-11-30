<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Helpers\SendEmails;
use App\Models\PqrsClients;
use Exception;

class PQRSController extends Controller
{
    public function __construct(Request $request)
    {
        /*$this->middleware('permission:/list_pqrs')->only(['show', 'index']);
        $this->middleware('permission:/update_pqrs')->only(['update', 'destroy']);*/

        // Get the languaje id
        $language = Language::find($request->header('language-key'));
        if($language){
            $this->language = $request->header('language-key');
        }else if($request->header('language-key') == ''){
            $this->language = '';
        }else{
            $this->language = 1;
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = PqrsClients::range(request('date_start'), request('date_end'))
        ->get();

        return response()->json(['response' => $clients]);
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
            'last_name' => 'required|min:1|max:75',
            'email' => 'required|email',
            'cell_phone' => 'required|max:15',
            'pqrs_id' => 'required|exists:contact_type,id',
            'message' => 'required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        # Find pqrs
        $pqrs = DB::table('contact_type')->find(request('pqrs_id'));

        # Data of the user who performs the pqrs
        $principal_email = array((object)['email' => request('email'), 'name' => request('name')." ".request('last_name')]);

        DB::beginTransaction();
        try{
            $new_client = PqrsClients::create([
                'name' => request('name'),
                'last_name' => request('last_name'),
                'email' => request('email'),
                'cell_phone' => request('cell_phone'),
            ]);

            $client_id = $new_client->id;

            # We generate the data to send the mail to the created user
            $data = array(
                'name' => request('name')." ".request('last_name'),
                'email' => request('email'),
                'pqrs' => $pqrs->name,
                'message' => request('message'),
                'pqrs_id' => $client_id
            );
            # Send email

            # Send Notification
            $mail = Mail::to(request('email'))->send(new SendEmails('pqrs_client', 'Seguimiento de tu pedido.', 'noreply@mosbeautyshop.com', $data));

            if($mail){
                return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
            }

            /*$send_email = SendEmailHelper::sendEmail('Correo de pqrs.', TemplatesHelper::pqrsData($data), $principal_email, array());
            if($send_email != 1){
                return response()->json(['response' => ['error' => [$send_email]]], 400);
            }*/

            # We generate the data to send the mail to the created user
            $data_2 = array(
                'admin_name' => 'admin_name',
                'name' => request('name')." ".request('last_name'),
                'email' => request('email'),
                'cell_phone' => request('cell_phone'),
                'subject' => $pqrs->name,
                'description' => $pqrs->description,
                'pqrs' => $pqrs->name,
                'pqrs_id' => $client_id,
                'message' => request('message')
            );
            $principal_email = array((object)['email' => 'programador5@binar10.co', 'name' => 'Atención a el cliente']);

            # Send Notification
            $mail = Mail::to('programador5@binar10.co')->send(new SendEmails('pqrs_admin', 'Nuevo pqrs # '.$client_id, 'noreply@mosbeautyshop.com', $data_2));

            if($mail){
                return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
            }

            /*# Send email to admin
            $send_email = SendEmailHelper::sendEmail('Nuevo pqrs # '.$client_id, TemplatesHelper::pqrsDataAdmin($data_2), $principal_email, array());
            if($send_email != 1){
                return response()->json(['response' => ['error' => [$send_email]]], 400);
            }*/
        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al crear el cliente'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }

        DB::commit();
        return response()->json(['response' => 'Su solicitud a sido recibida, pronto estaremos en contacto con usted.'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pqrs = PqrsClients::find($id);

        return response()->json(['response' => $pqrs], 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
