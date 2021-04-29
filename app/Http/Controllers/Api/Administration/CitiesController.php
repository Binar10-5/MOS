<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitiesController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_cities')->only(['show', 'index']);
        $this->middleware('permission:/create_cities')->only(['store']);
        $this->middleware('permission:/update_cities')->only(['update', 'destroy', 'deliveryFee', 'deliveryFeeGet']);

        // Get the languaje id
        $language = Language::select('languages.id', 'c.id as country_id')
        ->join('countries as c', 'languages.id', 'c.language_id')
        ->where('c.id' ,$request->header('language-key'))
        ->first();
        if($language){
            $this->language = $language->id;
            $this->country = $language->country_id;
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
            $cities = City::select('city.id', 'city.dane_code', 'city.name', 'city.department_dane_code', 'city.department_name', 'city.region_name', 'city.delivery_fee',
            'city.delivery_time', 'city.state', 'city.country_id')
            ->name(request('name'))
            ->state(request('state'))
            ->where('country_id', $this->country)
            ->paginate(8);
        }else{
            $cities = City::select('city.id', 'city.dane_code', 'city.name', 'city.department_dane_code', 'city.department_name', 'city.region_name', 'city.delivery_fee',
            'city.delivery_time', 'city.state', 'city.country_id')
            ->name(request('name'))
            ->state(request('state'))
            ->where('country_id', $this->country)
            ->get();
        }


        return response()->json(['response' => $cities], 200);
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
            $city = City::where('contry_id', $this->country)->find($id);

        return response()->json(['response' => $city], 200);
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
            'state' => 'required',
            'delivery_fee' => 'required',
            'delivery_time' => 'required',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $city = City::where('country_id', $this->country)->find($id);

        if(!$city){
            return response()->json(['response' => ['error' => ['Ciudad no encontrada']]], 400);
        }

        $city->delivery_fee = request('delivery_fee');
        $city->delivery_time = request('delivery_time');
        $city->state = request('state');
        $city->update();

        return response()->json(['response' => $city], 200);
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

    public function deliveryFee(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'delivery_fee' => 'required',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $delivery = DB::table('delivery_fee_minimum')->where('country_id', $this->country)->update(['delivery_fee' => (int)request('delivery_fee'), 'updated_at' => date('Y-m-d H:i:s')]);

        return response()->json(['response' => 'Success'], 200);
    }

    public function deliveryFeeGet()
    {

        $delivery = DB::table('delivery_fee_minimum')->where('country_id', $this->country)->first();

        return response()->json(['response' => $delivery], 200);
    }
}
