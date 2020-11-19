<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderProducts;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_orders')->only(['show', 'index']);
        $this->middleware('permission:/create_orders')->only(['store']);
        $this->middleware('permission:/update_orders')->only(['update']);

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
        if(request('paginate')){
            $orders = Order::code(request('code'))
            ->state(request('state_id'))
            ->total(request('total_min'), request('total_max'))
            ->subtotal(request('subtotal_min'), request('subtotal_max'))
            ->created(request('date_start'), request('date_end'))
            ->paginate(8);
        }else{
            $orders = Order::code(request('code'))
            ->state(request('state_id'))
            ->total(request('total_min'), request('total_max'))
            ->subtotal(request('subtotal_min'), request('subtotal_max'))
            ->created(request('date_start'), request('date_end'))
            ->get();
        }

        return response()->json(['response' => $orders], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);

        if(!$order){
            return response()->json(['response' => ['error' => ['Pedido no encontrado']]], 400);
        }

        $order->products = OrderProducts::select('pv.name', 'pv.color', 'pv.principal_id', 'pv.price', 'pv.discount', 'pv.final_price', 'orders_products.quantity')
        ->join('product_variants as pv', 'orders_products.product_id', 'pv.id')
        ->join('orders as o', 'orders_products.order_id', 'o.id')
        ->where('o.id', $order->id)
        ->get();

        return response()->json(['response' => $order], 200);
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
