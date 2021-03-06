<?php

namespace App\Http\Controllers\Api\Administration\ExternalConnections;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\Error;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\OrderState;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Helpers\SendEmails;
use App\Models\ClientEmail;
class PayURequestController extends Controller
{
    public function getPaymentState(Request $request)
    {

        /*$order = Order::where('id', 1)->first();
        $order->payment_data = json_encode($request->all());
        $order->update();
        return response()->json(['response' => 'Success'], 200);*/

        $error =null;
        $order = Order::where('order_number', request('reference_sale'))->first();
        # We generate the data to send the mail to the factured pay
        /*$data = array(
            'name' => 'Ronaldo',
            'order_number' => $order->order_number,
            'numeral' => '#',
        );

        # Send Notification
        $mail = Mail::to('projectmanager@binar10.co')->send(new SendEmails('payment_approved', 'Pago aprobado.', 'noreply@mosbeautyshop.com', $data));
        return 1;
        if($mail){
            return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
        }*/

        DB::beginTransaction();
        try{
            if(!$order){
                $error = 1;
                throw new Exception("No se encontró la orden que manda el reference_sale de payU ". request('reference_sale'));
                Error::create([
                    'description'=> 'No se encontró la orden que manda el reference_sale de payU '. request('reference_sale'),
                    'type'=> 1
                ]);
                return response()->json(['response' => ['error' => ['Orden no existente']]], 400);
            }
            if(request('response_message_pol') != 'APPROVED'){
/*
                $products = OrderProducts::where('order_id', $order->id)->get();

                foreach ($products as $product) {
                    $variant = ProductVariant::find($product->product_id);

                    if(!$variant){
                        $error = 2;
                        throw new Exception("No se encontró la variante de el producto en el momento de devolver el inventario, el id es el: ".$product->product_id);
                        Error::create([
                            'description'=> 'No se encontró la variante de el producto en el momento de devolver el inventario, el id es el: '.$product->product_id,
                            'type'=> 2
                        ]);
                        return response()->json(['response' => ['error' => ['Variante de producto no encontradaa']]], 400);
                    }

                    $variant->quantity += $product->quantity;
                    $variant->update();
                }

                if($order->coupon_id != null || $order->coupon_id != ''){
                    $coupon = Cupon::find($order->coupon_id);
                    $coupon->uses_number -= 1;
                    $coupon->update();
                }

                $state = OrderState::find($order->state_id);
                $new_state = OrderState::find(2);
                $new_tracking = json_decode($order->tracking);

                array_push($new_tracking, array(
                    'last_id'=> $state->id,
                    'last_state'=> $state->name,
                    'state_id'=> $new_state->id,
                    'state'=> $new_state->name,
                    'state_date'=> date('Y-m-d H:i:s'),
                    'reason'=> request('error_message_bank')
                ));

                $order->payment_data = json_encode($request->all());
                $order->state_id = 2;
                $order->tracking = json_encode($new_tracking);
                $order->update();
*/
            }else{

                $products = OrderProducts::where('order_id', $order->id)->get();

                foreach ($products as $product) {
                    $variant = ProductVariant::find($product->product_id);

                    if(!$variant){
                        $error = 2;
                        throw new Exception("No se encontró la variante de el producto en el momento de devolver el inventario, el id es el: ".$product->product_id);
                        Error::create([
                            'description'=> 'No se encontró la variante de el producto en el momento de devolver el inventario, el id es el: '.$product->product_id,
                            'type'=> 2
                        ]);
                        return response()->json(['response' => ['error' => ['Variante de producto no encontrada']]], 400);
                    }

                    $variant->quantity -= $product->quantity;
                    $variant->update();
                }

                if($order->coupon_id != null || $order->coupon_id != ''){
                    $coupon = Cupon::find($order->coupon_id);
                    $coupon->uses_number += 1;
                    $coupon->update();
                }

                if($order->coupon_id != null || $order->coupon_id != ''){
                    $coupon = Cupon::find($order->coupon_id);
                    if($coupon->uses_number >= $coupon->maximum_uses){
                        $coupon->state = 2;
                        $coupon->update();
                    }
                }

                $state = OrderState::find($order->state_id);
                $new_state = OrderState::find(3);
                $new_tracking = json_decode($order->tracking);

                if(isset(json_decode($order->tracking)[0]->discount_subscriber)){
                    $new_t = json_decode($order->tracking)[0]->discount_subscriber;
                }else{
                    $new_t = 0;
                }

                if($new_t != null && $new_t != 0){
                    $client = ClientEmail::where('email', $order->client_email)->where('used', 0)->first();
                    if($client){
                        $client->used = 1;
                        $client->dni = $order->client_dni;
                        $client->update();
                    }
                }

                array_push($new_tracking, array(
                    'last_id'=> $state->id,
                    'last_state'=> $state->name,
                    'state_id'=> $new_state->id,
                    'state'=> $new_state->name,
                    'state_date'=> date('Y-m-d H:i:s'),
                    'reason'=> ''
                ));

                $order->payment_data = json_encode($request->all());
                $order->state_id = 3;
                $order->facturation_date = date('Y-m-d H:i:s');
                $order->tracking = json_encode($new_tracking);
                $order->update();

                # We generate the data to send the mail to the factured pay
                $data = array(
                    'name' => $order->client_name,
                    'order_number' => $order->order_number,
                    'numeral' => '#',
                );

                # Send Notification
                $mail = Mail::to($order->client_email)->send(new SendEmails('payment_approved', 'Pago aprobado.', 'noreply@mosbeautyshop.com', $data));

                if($mail){
                    return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
                }
            }

        }catch(Exception $e){
            /*if($error == 1){
                DB::rollback();
                Error::create([
                    'description'=> 'No se encontró la orden que manda el reference_sale de payU '.request('reference_sale'),
                    'type'=> 1
                ]);
                return response()->json(['response' => ['error' => ['Orden no existente']]], 400);
            }else if($error == 2){
                DB::rollback();
                Error::create([
                    'description'=> 'No se encontró la variante de el producto en el momento de devolver el inventario, el id es el: '.$product->id,
                    'type'=> 2
                ]);

                return response()->json(['response' => ['error' => ['Variante de producto no encontradaa']]], 400);
            }*/
            DB::rollback();
            Error::create([
                'description'=> 'Error en recibir de payU '. $e->getMessage() . $e->getLine(),
                'type'=> 5
            ]);
            return response()->json(['response' => ['error' => ['Error de servisor']]], 400);
        }


        DB::commit();
        return response()->json(['response' => 'Success'], 200);
    }
}
