<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\TransportationCompany;
use Illuminate\Http\Request;

class TransportationCompaniesController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_transportations')->only(['show', 'index']);
        $this->middleware('permission:/create_transportations')->only(['store']);
        $this->middleware('permission:/update_transportations')->only(['update', 'destroy']);

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
            $transportations = TransportationCompany::select('transportation_companies.name', 'transportation_companies.description',
            'transportation_companies.country_id', 'transportation_companies.state')
            ->join('countries as c', 'transportation_companies.country_id', 'c.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('id', 'desc')
            ->where('c.id', $this->country)
            ->paginate(8);
        }else{
            $transportations = TransportationCompany::select('transportation_companies.name', 'transportation_companies.description',
            'transportation_companies.country_id', 'transportation_companies.state')
            ->join('countries as c', 'transportation_companies.country_id', 'c.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('id', 'desc')
            ->where('c.id', $this->country)
            ->get();
        }

        return response()->json(['response' => $transportations], 200);
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
            'name' => 'required|min:1|max:75|unique:role,name',
            'description' => 'bail',
            'state' => 'bail|required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $transportation = TransportationCompany::create([
            'name' => request('name'),
            'description' => request('description'),
            'state' => request('state'),
            'country_id' => $this->country
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
        $transportation = TransportationCompany::select('transportation_companies.name', 'transportation_companies.description',
        'transportation_companies.country_id', 'transportation_companies.state')
        ->join('countries as c', 'transportation_companies.country_id', 'c.id')
        ->where('c.id', $this->country)
        ->first();

        return response()->json(['response' => $transportation], 200);

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
            'name' => 'required|min:1|max:100',
            'description' => 'bail',
            'state' => 'bail|required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $transportation = TransportationCompany::where('id', '!=', 1)->find($id);

        if(!$transportation){
            return response()->json(['response' => ['error' => ['Transportadora, no encontrada']]], 400);
        }

        $transportation->name = request('name');
        $transportation->description = request('description');
        $transportation->state = request('state');
        $transportation->country_id = $this->country;
        $transportation->update();

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
