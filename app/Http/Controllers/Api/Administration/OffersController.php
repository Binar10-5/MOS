<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Offer;
use Illuminate\Http\Request;

class OffersController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_cupon')->only(['show', 'index']);
        $this->middleware('permission:/create_cupon')->only(['store']);
        $this->middleware('permission:/update_cupon')->only(['update', 'destroy']);

        // Get the languaje id
        $language = Language::select('languages.id', 'c.id as country_id')
        ->join('countries as c', 'languages.id', 'c.language_id')
        ->where('c.id' ,$request->header('language-key'))
        ->first();
        if($language){
            $this->language = $language->id;
            $this->country = $language->country_id;
        }else if($request->header('language-key') == ''){
            $this->country = '';
            $this->language = '';
        }else{
            $this->country = $language->country_id;
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
        $offers = Offer::where('country_id', $this->country)->get();

        return response()->json(['response' => $offers], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $regex = "/^\d+(\.\d{1,2})?$/";

        $validator=\Validator::make($request->all(),[
            'name' => 'required|min:1',
            'description' => 'required',
            'minimal_cost' => 'required',
            'discount_amount' => 'required|numeric|regex:'.$regex,
            'state' => 'required|min:1|max:2',
            'maximum_cost' => 'required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $offer = Offer::create([
            'name' => request('name'),
            'description' => request('description'),
            'minimal_cost' => request('minimal_cost'),
            'maximum_cost' => request('maximum_cost'),
            'discount_amount' => request('discount_amount'),
            'state' => request('state'),
            'country_id' => $this->country,
        ]);

        return response()->json(['response' => $offer], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $offer = Offer::where('country_id', $this->country)->find($id);

        return response()->json(['response' => $offer], 200);
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
        $regex = "/^\d+(\.\d{1,2})?$/";

        $validator=\Validator::make($request->all(),[
            'discount_amount' => 'required|numeric|regex:'.$regex,
            'state' => 'required|min:1|max:2',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $offer = Offer::where('country_id', $this->country)->find($id);

        if(!$offer){
            return response()-json(['response' => ['error' => ['Oferta no encontrada']]], 400);
        }

        $offer->discount_amount = request('discount_amount');
        $offer->state = request('state');
        $offer->update();

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
