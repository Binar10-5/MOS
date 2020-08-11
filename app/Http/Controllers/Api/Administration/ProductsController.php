<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_products')->only(['show', 'index']);
        $this->middleware('permission:/create_products')->only(['store']);
        $this->middleware('permission:/update_products')->only(['update', 'destroy']);

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
            $products = Product::select('mp.id as principal_id', 'mp.name', 'mp.created_at', 'mp.updated_at')
            ->join('products_variant as vp', 'products.variant_id', 'vp.id')
            ->join('m_products as mp', 'vp.principal_id', 'mp.id')
            ->paginate(8);
        }else{
            $products = Product::select('mp.id as principal_id', 'mp.name', 'mp.created_at', 'mp.updated_at')
            ->join('products_variant as vp', 'products.variant_id', 'vp.id')
            ->join('m_products as mp', 'vp.principal_id', 'mp.id')
            ->get();
        }

        return response()->json(['response' => $products], 200);

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
