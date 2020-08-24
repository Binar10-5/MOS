<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Category3;
use App\Models\Language;
use App\Models\Master\MCategory3;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Categories3Controller extends Controller
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
            $categories = Category3::select('mc3.id as principal_id', 'mc3.name', 'mc3.state_id as entity_state_id')
            ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->category2(request('category2_id'))
            ->language($this->language)
            ->paginate(8);
        }else{
            $categories = Category3::select('mc3.id as principal_id', 'mc3.name', 'mc3.state_id as entity_state_id')
            ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->category2(request('category2_id'))
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
            'category2_id' => 'bail|required|integer|exists:m_categories_2,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        DB::beginTransaction();
        try{
            $principal_category = MCategory3::find(request('principal_id'));
            if($principal_category){
                $validate_language = Category3::where('principal_id', request('principal_id'))->where('language_id', $this->language)->first();
                if($validate_language){
                    return response()->json(['response' => ['error' => ['La categoría ya tiene un registro con este idioma.']]], 400);
                }else{
                    $principal_id = $principal_category->id;
                }
            }else{
                $m_caregory_3 = MCategory3::create([
                    'name' => request('name'),
                    'category2_id' => request('category2_id'),
                    'state_id' => request('entity_state_id'),
                ]);

                $principal_id = $m_caregory_3->id;
            }

            $category = Category3::create([
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
        $category = Category3::select('categories_3.id as son_id', 'categories_3.name', 'categories_3.description', 'categories_3.list_order', 'categories_3.principal_id', 'categories_3.language_id', 'mc3.state_id as entity_state_id', 'categories_3.state_id', 'mc3.category2_id')
        ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
        #->name(request('name'))
        #->state(request('state'))
        ->orderBy('categories_3.list_order', 'ASC')
        ->where('categories_3.language_id', $this->language)
        ->where('mc3.id', $id)
        ->first();


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
            'category2_id' => 'bail|required|integer|exists:m_categories_2,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $category = Category3::where('principal_id', $id)->where('language_id', $this->language)->first();

        $m_category_3 = MCategory3::find($id);

        $m_category_3->category2_id = request('category2_id');
        $m_category_3->update();

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
        $principal_category = MCategory3::find($id);

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
