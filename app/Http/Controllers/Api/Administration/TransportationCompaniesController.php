<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
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
            $transportations = TransportationCompany::name(request('name'))
            ->state(request('state'))
            ->orderBy('id', 'desc')
            ->paginate(8);
        }else{
            $transportations = TransportationCompany::name(request('name'))
            ->state(request('state'))
            ->orderBy('id', 'desc')
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
        $transportation = TransportationCompany::find($id);

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
