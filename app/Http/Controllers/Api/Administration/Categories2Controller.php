<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Category2;
use App\Models\Category3;
use App\Models\Language;
use App\Models\Master\MCategory2;
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
        $language = Language::select('languages.id')
        ->join('countries as c', 'languages.id', 'c.language_id')
        ->where('c.id' ,$request->header('language-key'))
        ->first();
        if($language){
            $this->language = $language->id;
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
            $categories = Category2::select('mc2.id as principal_id', 'mc2.name', 'mc2.state_id as entity_state_id', 'categories_2.image')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->category1(request('category1_id'))
            ->language($this->language)
            ->paginate(8);
        }else{
            $categories = Category2::select('mc2.id as principal_id', 'mc2.name', 'mc2.state_id as entity_state_id', 'categories_2.image')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->category1(request('category1_id'))
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
            'name' => 'required|min:1|max:75|unique:categories_2,name',
            'description' => 'required',
            'state_id' => 'required|integer|exists:categories_states,id',
            'entity_state_id' => 'required|integer',
            'image' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            'principal_id' => 'bail',
            'category1_id' => 'bail|required|integer|exists:m_categories_1,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        DB::beginTransaction();
        try{
            $principal_category = MCategory2::find(request('principal_id'));
            if($principal_category){
                $validate_language = Category2::where('principal_id', request('principal_id'))->where('language_id', $this->language)->first();
                if($validate_language){
                    return response()->json(['response' => ['error' => ['La categoría ya tiene un registro con este idioma.']]], 400);
                }else{
                    $principal_id = $principal_category->id;
                }
            }else{
                $m_caregory_2 = MCategory2::create([
                    'name' => request('name'),
                    'category1_id' => request('category1_id'),
                    'state_id' => request('entity_state_id'),
                ]);

                $principal_id = $m_caregory_2->id;
            }

            $language = Language::find($request->header('language-key'));
            $public_id = str_replace(' ', '-', $language->name.'-'.$principal_id.'-'.request('name'));

            # Here we upload an image 1
            $img_1 = \Cloudinary\Uploader::upload(request('image'),
            array(
                "folder" => "MOS/categories-2/".$language->name,
                "public_id" => $public_id
            ));


            $category = Category2::create([
                'name' => request('name'),
                'description' => request('description'),
                'list_order' => 1,
                'principal_id' => $principal_id,
                'image' => $img_1['secure_url'],
                'public_id' => $public_id,
                'language_id' => $this->language,
                'state_id' => request('state_id'),
                ]);

            if(!$category){
                # If there is a problem delete the cloud photos
                $api = new \Cloudinary\Api();
                $api->delete_resources(array($img_1['public_id']));
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
        $category = Category2::select('categories_2.id as son_id', 'categories_2.name', 'categories_2.description', 'categories_2.list_order', 'categories_2.principal_id', 'categories_2.language_id', 'mc2.state_id as entity_state_id', 'categories_2.state_id', 'mc2.category1_id', 'categories_2.image')
        ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
        #->name(request('name'))
        #->state(request('state'))
        ->orderBy('categories_2.list_order', 'ASC')
        ->where('categories_2.language_id', $this->language)
        ->where('mc2.id', $id)
        ->first();

        if($category){
            $categories3 = Category3::select('categories_3.id as son_id', 'categories_3.name', 'categories_3.description', 'categories_3.list_order', 'categories_3.language_id', 'categories_3.principal_id', 'mc3.state_id as entity_state_id', 'categories_3.state_id')
            ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
            ->join('m_categories_2 as mc2', 'mc3.category2_id', 'mc2.id')
            ->where('categories_3.language_id', $this->language)
            ->where('mc3.category2_id', $category->principal_id)
            ->get();

            $category->categories_3 = $categories3;
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
            'change_image' => 'required|boolean',
            'state_id' => 'required|integer|exists:categories_states,id',
            'category1_id' => 'bail|required|integer|exists:m_categories_1,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }
        $language = Language::find($request->header('language-key'));

        $category = Category2::where('principal_id', $id)->where('language_id', $this->language)->first();

        if(!$category){
            return response()->json(['response' => ['error' => ['Categoría no encontrada']]], 400);
        }

        $m_category_2 = MCategory2::find($id);
        DB::beginTransaction();
        try{

            $m_category_2->category1_id = request('category1_id');
            $m_category_2->update();

            $category->name = request('name');
            $category->description = request('description');
            $category->state_id = request('state_id');

            if(request('change_image')){
                $validator=\Validator::make($request->all(),[
                    'image' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('image'),
                array(
                    "folder" => "MOS/categories-2/".$language->name,
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $category->public_id,
                    "invalidate"=> true
                ));

                $category->image = $upload['secure_url'];
            }
            $category->update();

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['response' => ['error' => $e->getMessage(), $e->getLine()]], 400);
        }

        DB::commit();
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
        $principal_category = MCategory2::find($id);

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
