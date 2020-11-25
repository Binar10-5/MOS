<?php

namespace App\Http\Controllers\Api\Administration\ExternalConnections;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\Error;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayURequestController extends Controller
{
    public function getPaymentState(Request $request)
    {

        $order = Order::find(1);

        $order->payment_data = json_encode($request->all());
        $order->update();
        return response()->json(['response' => 'Success'], 200);


        $order = Order::where('order_number', request('reference_sale'))->first();

        if(!$order){
            Error::cretae([
                'description'=> 'No se encontró la orden que manda el reference_sale de payU '.request('reference_sale'),
                'type'=> 1
            ]);
            return response()->json(['response' => ['error' => ['Orden no existente']]], 400);
        }

        DB::beginTransaction();
        try{
            if(request('response_message_pol') != 'APPROVED'){
                $products = OrderProducts::where('order_id', $order->id)->get();

                foreach ($products as $product) {
                    $variant = ProductVariant::find($product->id);

                    if(!$variant){
                        Error::cretae([
                            'description'=> 'No se encontró la variante de el producto en el momento de devolver el inventario, el id es el: '.$product->id,
                            'type'=> 2
                        ]);
                        return response()->json(['response' => ['error' => ['Variante de producto no encontrada']]], 400);
                    }

                    $variant->quantity += $product->quantity;
                    $variant->update();
                }

                if($order->coupon_id != null || $order->coupon_id != ''){
                    $coupon = Cupon::find($order->coupon_id);
                    $coupon->uses_number -= 1;
                    $coupon->update();
                }

                $order->payment_data = json_encode($request->all());
                $order->state_id = 2;
                $order->update();
            }else{
                if($order->coupon_id != null || $order->coupon_id != ''){
                    $coupon = Cupon::find($order->coupon_id);
                    if($coupon->uses_number >= $coupon->maximum_uses){
                        $coupon->state = 2;
                        $coupon->update();
                    }
                }

                $order->payment_data = json_encode($request->all());
                $order->state_id = 3;
                $order->update();
            }

        }catch(Exception $e){
            DB::rollback();
        }

        DB::commit();
        return response()->json(['response' => 'Success'], 200);
    }
}
