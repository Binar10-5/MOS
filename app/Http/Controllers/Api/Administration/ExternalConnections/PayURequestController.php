<?php

namespace App\Http\Controllers\Api\Administration\ExternalConnections;

use App\Http\Controllers\Controller;
use App\Models\Error;
use App\Models\Order;
use Illuminate\Http\Request;

class PayURequestController extends Controller
{
    public function getPaymentState(Request $request)
    {

        $order = Order::find(1);

        $order->payment_data = json_encode($request->all());
        $order->update();
        return response()->json(['response' => 'Success'], 200);
        $validator=\Validator::make($request->all(),[
            'payment_state' => 'required',
            'description' => 'required|min:1',
            'order_id' => 'required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $order = Order::find(request('order_id'));

        if(request('payment_state') != 3){

        }else{

        }
    }
}
