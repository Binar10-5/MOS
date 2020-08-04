<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Category2;
use App\Models\Category3;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class Categories2Controller extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_category')->only(['show', 'index']);
        $this->middleware('permission:/create_category')->only(['store']);
        $this->middleware('permission:/update_category')->only(['update', 'destroy']);

        // Get the languaje id
        $language = Language::find($request->header('language-key'));
        if($language){
            $this->language = $request->header('language-key');
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
            $categories = Category2::select('categories_2.id as son_id', 'categories_2.name', 'categories_2.description', 'categories_2.list_order', 'categories_2.principal_id', 'categories_2.language_id', 'mc2.state_id as entity_state_id', 'categories_2.state_id')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            #->name(request('name'))
            #->state(request('state'))
            ->orderBy('categories_2.list_order', 'ASC')
            ->where('categories_2.language_id', $this->language)
            ->paginate(8);
        }else{
            $categories = Category2::select('categories_2.id as son_id', 'categories_2.name', 'categories_2.description', 'categories_2.list_order', 'categories_2.principal_id', 'categories_2.language_id', 'mc2.state_id as entity_state_id', 'categories_2.state_id')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            #->name(request('name'))
            #->state(request('state'))
            ->orderBy('categories_2.list_order', 'ASC')
            ->where('categories_2.language_id', $this->language)
            ->get();
        }

        foreach ($categories as $category) {
            $categories3 = Category3::select('categories_3.id as son_id', 'categories_3.name', 'categories_3.description', 'categories_3.list_order', 'categories_3.language_id', 'categories_3.principal_id', 'mc3.state_id as entity_state_id', 'categories_3.state_id')
            ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
            ->join('m_categories_2 as mc2', 'mc3.category2_id', 'mc2.id')
            ->where('categories_3.language_id', $this->language)
            ->where('mc3.category1_id', $category->principal_id)
            ->get();

            $category->categories_3 = $categories3;
        }

        return response()->json(['response' => $categories], 200);
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
