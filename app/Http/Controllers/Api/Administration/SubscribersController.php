<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\ClientEmail;
use App\Models\Language;
use Illuminate\Http\Request;

class SubscribersController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_subscribers')->only(['show', 'index']);

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
            $subscribers = ClientEmail::email(request('email'))
            ->range(request('date_start'), request('date_end'))
            ->orderBy('id', 'desc')
            ->where('country_id', $this->country)
            ->paginate(8);
        }else{
            $subscribers = ClientEmail::email(request('email'))
            ->range(request('date_start'), request('date_end'))
            ->orderBy('id', 'desc')
            ->where('country_id', $this->country)
            ->get();
        }

        return response()->json(['response' => $subscribers], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
