<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_orders')->only(['show', 'index']);
        #$this->middleware('permission:/create_orders')->only(['store']);
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
        $validator=\Validator::make($request->all(),[
            'client_name' => 'required',
            'client_last_name' => 'required',
            'client_address' => 'bail|required',
            'client_cell_phone' => 'bail|required',
            'client_email' => 'bail|required',
            'products_list' => 'bail|required|array',
            'coupon' => 'bail',
            'city_id' => 'bail|required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $subtotal = 0;
        foreach (request('products_list') as $product) {

            $variant = ProductVariant::find($product['id']);

            if(!$variant){
                return response()->json(['response' => ['error' => ['La variante de el producto no existe', $product]]], 400);
            }


            if($variant->quantity < $product['quantity']){
                return response()->json(['response' => ['error' => ['Lo sentimos en el momento de efectuar la compra nos quedamos sin la existencia de el productos solictado.', $product]]], 400);
            }
            # MIRAR AQUÍ
            # Poner el final price en el precio
            $subtotal += $variant->price * $product['quantity'];

        }
        DB::beginTransaction();
        try{
            $total = $subtotal;
            $coupon = null;
            if(!empty(request('coupon'))){
                $validate_coupon = Cupon::where('code', request('coupon'))->first();

                if(!$validate_coupon){
                    return response()->json(['response' => ['error' => ['El cupón no existe']]], 400);
                }

                if($validate_coupon->uses_number <= 0){
                    return response()->json(['response' => ['error' => ['El cupón ya alcanzó un limite de usos']]], 400);
                }

                if($validate_coupon->minimal_cost > $subtotal){
                    return response()->json(['response' => ['error' => ['El costo de el pedido tiene que ser mayor a '.$validate_coupon->minimal_cost.' para poder usar el cupón']]], 400);
                }

                $validate_coupon->uses_number -= 1;
                $total -= $validate_coupon->discount_amount;
                $coupon = $validate_coupon->id;
                $validate_coupon->update();
            }


            $order_number = Order::max('order_number') + 1;


            $order = Order::create([
                'order_number' => $order_number,
                'client_name' => request('client_name'),
                'client_last_name' => request('client_last_name'),
                'client_address' => request('client_address'),
                'client_cell_phone' => request('client_cell_phone'),
                'client_email' => request('client_email'),
                'subtotal' => $subtotal,
                'total' => $total,
                'state_id' => 1,
                'coupon_id' => $coupon,
                'city_id' => request('city_id'),
                'language_id' => $this->language
            ]);


            $valid_data = array();
            foreach (request('products_list') as $product) {

                $variant = ProductVariant::find($product['id']);

                if(!$variant){
                    return response()->json(['response' => ['error' => ['La variante de el producto no existe', $product]]], 400);
                }


                $validate_product = OrderProducts::where('order_id', $order->id)->where('product_id', $product['id'])->first();

                if(!$validate_product){
                    array_push($valid_data, [
                        'order_id' => $order->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                    ]);
                }

                $variant->quantity -= $product['quantity'];
                $variant->update();

            }

            $order_products = OrderProducts::insert($valid_data);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['response' => ['error' => [$e->getMessage()]]], 400);
        }
        DB::commit();
        return response()->json(['response' => $order->id], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $order = Order::select('orders.id', 'orders.order_number', 'orders.client_name', 'orders.client_last_name', 'orders.client_address', 'orders.client_cell_phone',
        'orders.client_email', 'orders.subtotal', 'orders.total', 'orders.state_id', 'orders.coupon_id', 'orders.transportation_company_id',
        'orders.tracking_number', 'orders.language_id', 'orders.payment_data', 'orders.city_id', 'c.name as city_name', 'c.department_name')
        ->join('city as c', 'orders.city_id', 'c.id')
        ->where('orders.id', $id)
        ->first();

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
