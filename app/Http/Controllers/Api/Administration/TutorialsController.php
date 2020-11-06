<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Master\MTutorial;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tutorial;
use App\Models\TutorialProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorialsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_tutorials')->only(['show', 'index']);
        $this->middleware('permission:/create_tutorials')->only(['store']);
        $this->middleware('permission:/update_tutorials')->only(['update', 'destroy']);

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
    public function index(Request $request)
    {
        if(request('paginate')){
            $tutorials = Tutorial::select('mt.title', 'mt.id as principal_id', 'mt.state', 'mt.description', 'tutorials.image', 'mt.created_at', 'mt.updated_at')
            ->join('m_tutorials as mt', 'tutorials.principal_id', 'mt.id')
            ->name(request('title'))
            ->state(request('state'))
            ->language($this->language)
            ->paginate(8);
        }else{
            $tutorials = Tutorial::select('mt.title', 'mt.id as principal_id', 'mt.state', 'mt.description', 'tutorials.image', 'mt.created_at', 'mt.updated_at')
            ->join('m_tutorials as mt', 'tutorials.principal_id', 'mt.id')
            ->name(request('title'))
            ->state(request('state'))
            ->language($this->language)
            ->get();
        }

        foreach ($tutorials as $tutorial) {
            $products = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
            'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5',
            'products.state_id', 'vp.new_product', 'vp.favorite', 'vp.cruelty_free')
            ->join('product_variants as vp', 'products.variant_id', 'vp.id')
            ->join('tutorial_products as tp', 'vp.id', 'tp.product_id')
            ->language($this->language)
            ->where('tp.tutorial_id', $tutorial->principal_id)
            ->get();

            $tutorial->products = $products;
        }

        return response()->json(['response' => $tutorials], 200);
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
            'title' => 'required|min:1|max:100',
            'description' => 'required',
            'image' => 'required',
            'content' => 'required',
            'slider' => 'bail',
            'principal_id' => 'bail',
            'products_list' => 'bail',
            'state' => 'bail|required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        DB::beginTransaction();
        try{
            $principal_tutorial = MTutorial::find(request('principal_id'));
            if($principal_tutorial){
                $validate_language = Tutorial::where('principal_id', request('principal_id'))->where('language_id', $this->language)->first();
                if($validate_language){
                    return response()->json(['response' => ['error' => ['El tutorial ya tiene un registro con este idioma.']]], 400);
                }else{
                    $principal_id = $principal_tutorial->id;
                }
            }else{
                $m_tutorial = MTutorial::create([
                    'title' => request('title'),
                    'description' => request('description'),
                    'state' => request('state'),
                ]);

                $principal_id = $m_tutorial->id;
            }

            $language = Language::find($request->header('language-key'));
            $public_id = str_replace(' ', '-', $language->name.'-'.$principal_id.'-'.request('title'));

            # Here we upload an image 1
            $img = \Cloudinary\Uploader::upload(request('image'),
            array(
                "folder" => "MOS/tutorials/".$principal_id.'/'.$language->name,
                "public_id" => $public_id
            ));


            $slider_array = array();
            for ($i=1; $i <= 10; $i++) {
                if(!empty(request('slider_'.$i))){
                    $slider_public_id = str_replace(' ', '-', $language->name.'-'.$principal_id.'-'.$i);

                    # Here we upload an image 1
                    $slider_img = \Cloudinary\Uploader::upload(request('slider_'.$i),
                    array(
                        "folder" => "MOS/tutorials/Sliders/".$principal_id.'/'.$language->name,
                        "public_id" => $slider_public_id
                    ));
                    array_push($slider_array, [
                        "image" => $slider_img['secure_url'],
                        "public_id" => $slider_public_id,
                        "is_change" => 0,
                    ]);
                }
            }

            /*foreach (request('slider') as $slider) {

                $slider_public_id = str_replace(' ', '-', $language->name.'-'.$principal_id);

                # Here we upload an image 1
                $slider_img = \Cloudinary\Uploader::upload($slider['image'],
                array(
                    "folder" => "MOS/tutorials/Sliders/".$language->name,
                    "public_id" => $slider_public_id
                ));
                array_push($slider_array, [
                    "image" => $slider_img['secure_url'],
                    "public_id" => $slider_public_id,
                    "is_change" => 0,
                ]);

            }*/



            $tutorial = Tutorial::create([
                'title' => request('title'),
                'description' => request('description'),
                'image' => $img['secure_url'],
                'public_id' => $public_id,
                'content' => request('content'),
                'slider' => json_encode($slider_array),
                'principal_id' => $principal_id,
                'language_id' => $this->language,
                'state' => request('state'),
            ]);

            if(!$tutorial){
                return response()->json(['response' => ['error' => ['Error al crear el tutorial']]], 400);
            }
            $valid_data = array();
            foreach (json_decode(request('products_list')) as $product) {

                $variant = ProductVariant::find($product);

                if(!$variant){
                    return response()->json(['response' => ['error' => ['La variante de el producto no existe', $product]]], 400);
                }


                $validate_product = TutorialProduct::where('tutorial_id', $principal_id)->where('product_id', $product)->first();

                if(!$validate_product){
                    array_push($valid_data, [
                        'tutorial_id' => $principal_id,
                        'product_id' => $product,
                        'state' => 1
                    ]);
                }

            }

            $tutorial_products = TutorialProduct::insert($valid_data);

        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al crear el tutorial'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
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
        $tutorial = Tutorial::select('mt.title', 'mt.id as principal_id', 'mt.state', 'mt.description', 'tutorials.image', 'tutorials.content', 'tutorials.slider',
        'tutorials.principal_id', 'tutorials.language_id', 'tutorials.state')
        ->join('m_tutorials as mt', 'tutorials.principal_id', 'mt.id')
        ->name(request('title'))
        ->state(request('state'))
        ->language($this->language)
        ->where('mt.id', $id)
        ->first();



        if($tutorial){
            $products = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
            'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5',
            'products.state_id', 'vp.new_product', 'vp.favorite', 'vp.cruelty_free')
            ->join('product_variants as vp', 'products.variant_id', 'vp.id')
            ->join('tutorial_products as tp', 'vp.id', 'tp.product_id')
            ->where('tp.tutorial_id', $tutorial->principal_id)
            ->language($this->language)
            ->get();

            $tutorial->products = $products;
        }

        return response()->json(['response' => $tutorial], 200);

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
            'title' => 'required|min:1|max:100',
            'description' => 'required',
            'change_img' => 'required|boolean',
            'change_slider' => 'required|boolean',
            'content' => 'required',
            'slider' => 'bail',
            'change_slider' => 'bail',
            'products_add' => 'bail|array',
            'products_remove' => 'bail|array',
            'state' => 'bail|required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $tutorial = Tutorial::where('principal_id', $id)->where('language_id', $this->language) ->first();

        if(!$tutorial){
            return response()->json(['response' => ['error' => 'Tutorial no encontrado']], 400);
        }
        DB::beginTransaction();
        try{

            $slider_array = request('slider');
            if(request('change_slider')){
                foreach ($slider_array as $slider) {
                    if($slider['is_change'] == 1){

                        $language = Language::find($request->header('language-key'));
                        #$slider_public_id = str_replace(' ', '-', $language->name.'-'.$id);

                        # Here we upload an image 1
                        $slider_img = \Cloudinary\Uploader::upload($slider['image'],
                        array(
                            "folder" => "MOS/tutorials/Sliders/".$language->name,
                            "public_id" => $slider['public_id']
                        ));
                        $slider['image'] = $slider_img['secure_url'];
                        $slider['public_id'] = $slider['public_id'];
                        $slider['is_change'] = 0;
                        /*array_push($slider_array, [
                            "image" => $slider_img['secure_url'],
                            "public_id" => $slider_img['public_id'],
                            "is_change" => 0,
                        ]);*/
                    }else if($slider['is_change'] == 2){
                        $language = Language::find($request->header('language-key'));
                        $slider_public_id = str_replace(' ', '-', $language->name.'-'.$id);

                        # Here we upload an image 1
                        $slider_img = \Cloudinary\Uploader::upload($slider['image'],
                        array(
                            "folder" => "MOS/tutorials/Sliders/".$language->name,
                            "public_id" => $slider_public_id
                        ));
                        array_push($slider_array, [
                            "image" => $slider_img['secure_url'],
                            "public_id" => $slider_img['public_id'],
                            "is_change" => 0,
                        ]);
                    }
                }
            }
            $tutorial->title = request('title');
            $tutorial->description = request('description');
            $tutorial->content = request('content');
            $tutorial->slider = $slider_array;
            $tutorial->state = request('state');

            if(request('change_img')){
                $validator=\Validator::make($request->all(),[
                    'image' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                  return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                $language = Language::find($request->header('language-key'));
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('image'),
                array(
                    "folder" => "MOS/Tutorials/.$language->name",
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $tutorial->public_id,
                    "invalidate"=> true
                ));
                $tutorial->image = $upload['secure_url'];

            }

            foreach (request('products_remove') as $delete_array) {

                $variant = ProductVariant::find($delete_array);

                if(!$variant){
                    return response()->json(['response' => ['error' => ['La variante de el producto no existe', $delete_array]]], 400);
                }

                $validate_product = TutorialProduct::where('tutorial_id', $id)->where('product_id', $delete_array)->first();

                if($validate_product){
                    $validate_product->delete();
                }
            }
            $valid_data = array();
            foreach (request('products_add') as $product) {

                $variant = ProductVariant::find($product);

                if(!$variant){
                    return response()->json(['response' => ['error' => ['La variante de el producto no existe', $product]]], 400);
                }


                $validate_product = TutorialProduct::where('tutorial_id', $id)->where('product_id', $product)->first();

                if(!$validate_product){
                    array_push($valid_data, [
                        'tutorial_id' => $id,
                        'product_id' => $product,
                        'state' => 1
                    ]);
                }

            }

            $tutorial_products = TutorialProduct::insert($valid_data);

        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al agregar el producto'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }
        $tutorial->update();
        # Here we return success.
        DB::commit();
        return response()->json(['response' => 'Tutorial actualizado.'], 200);
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
