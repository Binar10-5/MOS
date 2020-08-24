<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Category1;
use App\Models\Category2;
use App\Models\Language;
use App\Models\Master\MCategory1;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Categories1Controller extends Controller
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
            $categories = Category1::select('mc1.name', 'mc1.id as principal_id', 'mc1.state_id as entity_state_id')
            ->join('m_categories_1 as mc1', 'categories_1.principal_id', 'mc1.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->language($this->language)
            ->paginate(8);
        }else{
            $categories = Category1::select('mc1.name', 'mc1.id as principal_id', 'mc1.state_id as entity_state_id')
            ->join('m_categories_1 as mc1', 'categories_1.principal_id', 'mc1.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->language($this->language)
            ->get();
        }

        return response()->json(['response' => $categories], 200);
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
            'name' => 'required|min:1|max:75',
            'description' => 'required',
            'state_id' => 'required|integer|exists:categories_states,id',
            'entity_state_id' => 'required|integer',
            'principal_id' => 'bail',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        DB::beginTransaction();
        try{
            $principal_category = MCategory1::find(request('principal_id'));
            if($principal_category){
                $validate_language = Category1::where('principal_id', request('principal_id'))->where('language_id', $this->language)->first();
                if($validate_language){
                    return response()->json(['response' => ['error' => ['La categoría ya tiene un registro con este idioma.']]], 400);
                }else{
                    $principal_id = $principal_category->id;
                }
            }else{
                $m_caregory_1 = MCategory1::create([
                    'name' => request('name'),
                    'state_id' => request('entity_state_id'),
                ]);

                $principal_id = $m_caregory_1->id;
            }

            $category = Category1::create([
                'name' => request('name'),
                'description' => request('description'),
                'list_order' => 1,
                'principal_id' => $principal_id,
                'language_id' => $this->language,
                'state_id' => request('state_id'),
                ]);

            if(!$category){
                return response()->json(['response' => ['error' => ['Error al crear la categoria']]], 400);
            }

        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al crear la categoria'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }

        DB::commit();

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
        $category = Category1::select('categories_1.id as son_id', 'categories_1.name', 'categories_1.description', 'categories_1.list_order', 'categories_1.principal_id', 'categories_1.language_id', 'mc1.state_id as entity_state_id', 'categories_1.state_id')
        ->join('m_categories_1 as mc1', 'categories_1.principal_id', 'mc1.id')
        #->name(request('name'))
        #->state(request('state'))
        ->orderBy('categories_1.list_order', 'ASC')
        ->where('categories_1.language_id', $this->language)
        ->where('mc1.id', $id)
        ->first();

        if($category){
            $categories2 = Category2::select('categories_2.id as son_id', 'categories_2.name', 'categories_2.description', 'categories_2.list_order', 'categories_2.language_id', 'categories_2.principal_id', 'mc2.state_id as entity_state_id', 'categories_2.state_id')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            ->join('m_categories_1 as mc1', 'mc2.category1_id', 'mc1.id')
            ->where('categories_2.language_id', $this->language)
            ->where('mc2.category1_id', $category->principal_id)
            ->get();

            $category->categories_2 = $categories2;
        }

        return response()->json(['response' => $category], 200);
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
            'name' => 'required|min:1|max:75',
            'description' => 'required',
            'state_id' => 'required|integer|exists:categories_states,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $category = Category1::where('principal_id', $id)->where('language_id', $this->language)->first();

        if(!$category){
            return response()->json(['response' => ['error' => ['Categoría no encontrada']]], 400);
        }

        $category->name = request('name');
        $category->description = request('description');
        $category->state_id = request('state_id');
        $category->update();

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
        $principal_category = MCategory1::find($id);

        if(!$principal_category){
            return response()->json(['response' => ['error' => 'Categoría no encontrada']], 400);
        }

        if($principal_category->state_id == 1){
            $principal_category->state_id = 0;
        }else{
            $principal_category->state_id = 1;
        }
        $principal_category->update();
        return response()->json(['response' => 'Success'], 200);

    }
}
