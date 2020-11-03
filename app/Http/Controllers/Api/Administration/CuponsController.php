<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;

class CuponsController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_cupon')->only(['show', 'index']);
        $this->middleware('permission:/create_cupon')->only(['store']);
        $this->middleware('permission:/update_cupon')->only(['update', 'destroy']);

        // Get the languaje id
        /*$language = Language::find($request->header('language-key'));
        if($language){
            $this->language = $request->header('language-key');
        }else if($request->header('language-key') == ''){
            $this->language = '';
        }else{
            $this->language = 1;
        }*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request('paginate')){
            $cupons = Cupon::name(request('name'))
        ->state(request('state'))
        ->paginate(8);
        }else{
            $cupons = Cupon::name(request('name'))
        ->state(request('state'))
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
            'code' => 'bail|required|max:20',
            'uses_number' => 'bail|required|integer',
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

        $cupon = Cupon::create([
            'name' => request('name'),
            'description' => request('description'),
            'code' => request('code'),
            'uses_number' => request('uses_number'),
            'maximum_uses' => request('maximum_uses'),
            'minimal_cost' => request('minimal_cost'),
            'discount_amount' => request('discount_amount'),
            'state' => request('state'),
        ]);

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
        $cupon = Cupon::find($id);

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
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $cupon = Cupon::find($id);
        if(!$cupon){
            return response()->json(['response' => ['error' => ['Cupón no encontrado']]], 400);
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
