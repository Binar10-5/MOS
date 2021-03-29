<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\CouponCountry;
use App\Models\Cupon;
use App\Models\Language;
use Illuminate\Http\Request;

class CuponsController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_cupon')->only(['show', 'index']);
        $this->middleware('permission:/create_cupon')->only(['store']);
        $this->middleware('permission:/update_cupon')->only(['update', 'destroy']);

        // Get the languaje id
        $language = Language::select('languages.id')
        ->join('countries as c', 'languages.id', 'c.language_id')
        ->where('c.id' ,$request->header('language-key'))
        ->first();
        if($language){
            $this->language = $language->id;
            $this->country = $language->country_id;
        }else if($request->header('language-key') == ''){
            $this->language = '';
            $this->country = '';
        }else{
            $this->language = 1;
            $this->country = 1;
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
            $cupons = Cupon::select('cupons.name', 'cupons.description', 'cupons.code', 'cc.uses_number', 'cc.maximum_uses', 'cc.minimal_cost', 'cc.discount_amount', 'cupons.state')
            ->join('coupons_country as cc', 'cupons.id', 'cc.coupon_id')
            ->name(request('name'))
            ->state(request('state'))
            ->where('cc.country_id', $this->country)
            ->paginate(8);
        }else{
            $cupons = Cupon::select('cupons.name', 'cupons.description', 'cupons.code', 'cc.uses_number', 'cc.maximum_uses', 'cc.minimal_cost', 'cc.discount_amount', 'cupons.state')
            ->join('coupons_country as cc', 'cupons.id', 'cc.coupon_id')
            ->name(request('name'))
            ->state(request('state'))
            ->where('cc.country_id', $this->country)
            ->get();
        }

        return response()->json(['response' => $cupons], 200);
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
            'name' => 'required|min:1|max:200',
            'description' => 'required',
            'code' => 'bail|required|max:20|unique:cupons,code',
            'minimal_cost' => 'required|max:20',
            'maximum_uses' => 'required',
            'discount_amount' => 'required|max:20',
            'state' => 'required|min:1|max:2',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        if(request('discount_amount') > request('minimal_cost')){
            return response()->json(['response' => ['error' => ['El descuento no puede ser mayor a el costo minimo de el pedido']]], 400);
        }

        $coupon_id = Cupon::find(request('coupon_id'));

        if($coupon_id){
            $coupon_country = CouponCountry::create([
                'uses_number' => 0,
                'maximum_uses' => request('maximum_uses'),
                'minimal_cost' => request('minimal_cost'),
                'discount_amount' => request('discount_amount'),
                'state' => 1,
                'coupon_id' => $coupon_id->id,
                'country_id' => $this->country,
            ]);
        }else{
            $cupon = Cupon::create([
                'name' => request('name'),
                'description' => request('description'),
                'code' => request('code'),
                'uses_number' => 0,
                'maximum_uses' => request('maximum_uses'),
                'minimal_cost' => request('minimal_cost'),
                'discount_amount' => request('discount_amount'),
                'state' => request('state'),
            ]);

            $coupon_country = CouponCountry::create([
                'uses_number' => 0,
                'maximum_uses' => request('maximum_uses'),
                'minimal_cost' => request('minimal_cost'),
                'discount_amount' => request('discount_amount'),
                'state' => 1,
                'coupon_id' => $cupon->id,
                'country_id' => $this->country,
            ]);
        }



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
        $cupon = Cupon::select('cupons.name', 'cupons.description', 'cupons.code', 'cc.uses_number', 'cc.maximum_uses', 'cc.minimal_cost', 'cc.discount_amount', 'cupons.state')
        ->join('coupons_country as cc', 'cupons.id', 'cc.coupon_id')
        ->where('cc.country_id', $this->country)
        ->where('cupons.id', $id)
        ->first();

        return response()->json(['response' => $cupon], 200);
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
            /*'name' => 'required|min:1|max:200',
            'description' => 'required',
            'code' => 'bail|required|max:20',
            'uses_number' => 'bail|required|integer',
            'minimal_cost' => 'required|max:20',
            'discount_amount' => 'required|max:20',*/
            'state' => 'required|min:1|max:2',
            'state_country' => 'required|min:1|max:2',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $cupon = Cupon::find($id);
        if(!$cupon){
            return response()->json(['response' => ['error' => ['Cupón no encontrado']]], 400);
        }

        $coupon_country = CouponCountry::where('coupon_id', $cupon->id)->where('country_id', $this->country)->first();

        if($coupon_country){
            $coupon_country->state = request('state_country');
            $coupon_country->update();
        }

        /*$cupon->name = request('name');
        $cupon->description = request('description');
        $cupon->code = request('code');
        $cupon->uses_number = request('uses_number');
        $cupon->minimal_cost = request('minimal_cost');
        $cupon->discount_amount = request('discount_amount');*/
        $cupon->state = request('state');
        $cupon->update();

        return response()->json(['response' => 'Success'], 200);
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
