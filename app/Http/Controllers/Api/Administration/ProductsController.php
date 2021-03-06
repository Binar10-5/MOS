<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Category1;
use App\Models\Language;
use App\Models\Master\MCategory3;
use App\Models\Master\MCategory2;
use App\Models\Master\MCategory1;
use App\Models\Master\MProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantPrice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_products')->only(['show', 'index']);
        $this->middleware('permission:/create_products')->only(['store', 'productVariant']);
        $this->middleware('permission:/update_products')->only(['update', 'destroy']);

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
            $products = MProduct::select('m_products.id', 'm_products.name as product_name', 'm_products.state_id','b.id as brand_id', 'b.name as brand_name')
            ->join('brands as b', 'm_products.brand_id', 'b.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->category1(request('category1_id'))
            ->category2(request('category2_id'))
            ->category3(request('category3_id'))
            #->priceRange(request('min'), request('max'))
            #->favorite(request('favorite'))
            #->newProduct(request('new_product'))
            ->brand(request('brand'))
            #->discount(request('discount'))
            ->paginate(8);

            foreach ($products as $product) {
                $cat1 = MProduct::select('c1.id as category1_id', 'c1.name as category1_name')
                ->join('m_categories_1 as mc1', 'm_products.category1_id', 'mc1.id')
                ->join('categories_1 as c1', 'mc1.id', 'c1.principal_id')
                ->where('m_products.id', $product->id)
                ->languageC1($this->language)
                ->first();

                $cat2 = MProduct::select('c2.id as category2_id', 'c2.name as category2_name')
                ->join('m_categories_2 as mc2', 'm_products.category2_id', 'mc2.id')
                ->join('categories_2 as c2', 'mc2.id', 'c2.principal_id')
                ->where('m_products.id', $product->id)
                ->languageC2($this->language)
                ->first();

                $cat3 = MProduct::select('c3.id as category3_id', 'c3.name as category3_name')
                ->join('m_categories_3 as mc3', 'm_products.category3_id', 'mc3.id')
                ->join('categories_3 as c3', 'mc3.id', 'c3.principal_id')
                ->where('m_products.id', $product->id)
                ->languageC3($this->language)
                ->first();

                $product->categories1 = $cat1;
                $product->categories2 = $cat2;
                $product->categories3 = $cat3;
            }

            /*$products = Product::select('mp.id as principal_id', 'mp.name', 'mp.created_at', 'mp.updated_at')
            ->join('product_variants as vp', 'products.variant_id', 'vp.id')
            ->join('m_products as mp', 'vp.principal_id', 'mp.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            #->vState(request('v_state'))
            ->language($this->language)
            ->paginate(8);*/
        }else{
            $products = MProduct::select('m_products.id', 'm_products.name as product_name', 'm_products.state_id', 'mc1.id as category1_id', 'mc1.name as category1_name',
            'mc2.id as category2_id', 'mc2.name as category2_name',
            'mc3.id as category3_id', 'mc3.name as category3_name', 'b.id as brand_id', 'b.name as brand_name')
            ->join('m_categories_1 as mc1', 'm_products.category1_id', 'mc1.id')
            ->join('m_categories_2 as mc2', 'm_products.category2_id', 'mc2.id')
            ->join('m_categories_3 as mc3', 'm_products.category3_id', 'mc3.id')
            ->join('brands as b', 'm_products.brand_id', 'b.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            ->get();

            foreach ($products as $product) {
                $cat1 = MProduct::select('c1.id as category1_id', 'c1.name as category1_name')
                ->join('m_categories_1 as mc1', 'm_products.category1_id', 'mc1.id')
                ->join('categories_1 as c1', 'mc1.id', 'c1.principal_id')
                ->where('m_products', $product->id)
                ->languageC1($this->language)
                ->get();

                $cat2 = MProduct::select('c2.id as category2_id', 'c2.name as category2_name')
                ->join('m_categories_2 as mc2', 'm_products.category2_id', 'mc2.id')
                ->join('categories_2 as c2', 'mc2.id', 'c2.principal_id')
                ->where('m_products', $product->id)
                ->languageC2($this->language)
                ->get();

                $cat3 = MProduct::select('c3.id as category3_id', 'c3.name as category3_name')
                ->join('m_categories_3 as mc3', 'm_products.category3_id', 'mc3.id')
                ->join('categories_3 as c3', 'mc3.id', 'c3.principal_id')
                ->where('m_products', $product->id)
                ->languageC3($this->language)
                ->get();

                $product->categories1 = $cat1;
                $product->categories2 = $cat2;
                $product->categories3 = $cat3;
            }
            /*$products = Product::select('mp.id as principal_id', 'mp.name', 'mp.created_at', 'mp.updated_at')
            ->join('product_variants as vp', 'products.variant_id', 'vp.id')
            ->join('m_products as mp', 'vp.principal_id', 'mp.id')
            ->name(request('name'))
            ->mState(request('m_state'))
            #->vState(request('v_state'))
            ->language($this->language)
            ->get();*/
        }

        return response()->json(['response' => $products], 200);

    }

    /**
     * Store a newly created resource in storage.
     * Crear el "producto principal" que guarda y relaciona las variantes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'name' => 'required|min:1|max:75|unique:m_products,id',
            'state_id' => 'required|integer|min:0|max:1',
            'category1_id' => 'integer',
            'category2_id' => 'integer',
            'category3_id' => 'integer',
            'brand_id' => 'required|integer|exists:brands,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $category_3 = MCategory3::find(request('category3_id'));
        $category_2 = MCategory2::find(request('category2_id'));
        $category_1 = MCategory1::find(request('category1_id'));
        if($category_1){
            $category1 = $category_1->id;
        }else{
            $category1 = null;
        }

        if($category_2){
            $category2 = $category_2->id;
        }else{
            $category2 = null;
        }

        if($category_3){
            $category3 = $category_3->id;
        }else{
            $category3 = null;
        }

        $m_product = MProduct::create([
            'name' => request('name'),
            'category1_id' => $category1,
            'category2_id' => $category2,
            'category3_id' => $category3,
            'brand_id' => request('brand_id'),
            'state_id' => request('state_id'),
        ]);

        return response()->json(['response' => 'Success'], 200);
    }

    /* Crear variante de el producto y generar automaticamente el idioma a el que se quiere registrar */
    public function productVariants(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'variant_id' => 'bail',
            'name' => 'required|min:1|max:75|unique:product_variants,id',
            'description' => 'required',
            'color_code' => 'bail',
            'color' => 'bail',
            'how_to_use' => 'required',
            'benefits' => 'required',
            'principal_id' => 'required|integer|exists:m_products,id',
            'img_1' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            'favorite' => 'required',
            'new_product' => 'required',
            'cruelty_free' => 'required',
            'state_id' => 'required|integer|min:0|max:2',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        DB::beginTransaction();
        try {

            $product_variant = ProductVariant::find(request('variant_id'));
            if($product_variant){
                $validate_language = Product::where('variant_id', request('variant_id'))->where('language_id', $this->language)->first();
                if($validate_language){
                    return response()->json(['response' => ['error' => ['La variante de el producto ya tiene un registro con este idioma.']]], 400);
                }else{
                    $variant_id = $product_variant->id;
                }
            }else{
                # Obtener el maximo de las posiciones por cat, para ponerlo en el ultimo lugar

                // Validar que el discount sea de 0 a 1 con 0.01, 0.02... 0.99
                $regex = "/^\d+(\.\d{1,2})?$/";
                $validator=\Validator::make($request->all(),[
                    'quantity' => 'required|integer|max:1000000',
                    'price' => 'required|regex:'.$regex,
                    'discount' => 'required|numeric|regex:'.$regex,
                ]);
                if($validator->fails())
                {
                    return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }

                $m_product = MProduct::find(request('principal_id'));

                $max_cat1 = ProductVariant::select('product_variants.id', 'product_variants.category1_order')
                ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                ->where('mp.category1_id', $m_product->category1_id)
                ->max('category1_order');
                $max_cat2 = ProductVariant::select('product_variants.id', 'product_variants.category2_order')
                ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                ->where('mp.category2_id', $m_product->category2_id)
                ->max('category2_order');
                $max_cat3 = ProductVariant::select('product_variants.id', 'product_variants.category3_order')
                ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                ->where('mp.category3_id', $m_product->category3_id)
                ->max('category3_order');

                if(request('discount') != 0){
                    if(request('discount') >= 2){
                        $discount = 1;
                    }else{
                        $discount = request('discount');
                    }
                    if($this->country == 2){
                        $price = request('price');
                        $price_discount = bcdiv(($price * $discount), "1", 2);

                        $final_price_not_roud = $price - $price_discount;

                        $final_price = bcdiv ($final_price_not_roud , "1" , 2);
                    }else{
                        $price = request('price');
                        $price_discount = ($price * $discount);

                        $final_price = $price - $price_discount;
                    }

                    #return response()->json(['response' => [$final_price, $price, $discount, $price_discount, ceil($final_price)]], 200);
                }else{
                    $discount = 0;
                    $final_price = request('price');
                }

                $variant = ProductVariant::create([
                    'name' => request('name'),
                    'color_code' => request('color_code'),
                    'color' => request('color'),
                    'principal_id' => request('principal_id'),
                    'quantity' => request('quantity'),
                    'price' => request('price'),
                    'discount' => $discount,
                    'final_price' => $final_price,
                    'category1_order' => $max_cat1+1,
                    'category2_order' => $max_cat2+1,
                    'category3_order' => $max_cat3+1,
                    'favorite' => request('favorite'),
                    'new_product' => request('new_product'),
                    'cruelty_free' => request('cruelty_free'),
                    'state_id' => request('state_id'),
                ]);

                $variant_id = $variant->id;
                $price_variant_validator = VariantPrice::where('country_id', $this->language)->where('variant_id', $variant_id)->first();
                if(!$price_variant_validator){
                    $price_variant = VariantPrice::create([
                        'price'=> request('price'),
                        'discount'=> $discount,
                        'final_price'=> $final_price,
                        'country_id'=> $this->country,
                        'variant_id'=> $variant_id
                    ]);
                }
            }
            # Agregar registro de la variante por idioma.
            $language = Language::find($this->language);
            $public_id = str_replace(' ', '-', $language->name.'-'.$variant_id.'-'.request('name'));

            # Here we upload an image 1
            $img_1 = \Cloudinary\Uploader::upload(request('img_1'),
            array(
                "folder" => "MOS/products/".$language->name,
                "public_id" => $public_id."-1"
            ));

            if(request('img_2') != '' || request('img_2') != null){
                $validator=\Validator::make($request->all(),[
                    'img_2' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload an image 2
                $img_2 = \Cloudinary\Uploader::upload(request('img_2'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    "public_id" => $public_id."-2"
                ));
            }else{
                $img_2 = array(
                    'secure_url' => ''
                );
            }

            if(request('img_3') != '' || request('img_3') != null){
                $validator=\Validator::make($request->all(),[
                    'img_3' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload an image 3
                $img_3 = \Cloudinary\Uploader::upload(request('img_3'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    "public_id" => $public_id."-3"
                ));
            }else{
                $img_3 = array(
                    'secure_url' => ''
                );
            }

            if(request('img_4') != '' || request('img_4') != null){
                $validator=\Validator::make($request->all(),[
                    'img_4' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload an image 4
                $img_4 = \Cloudinary\Uploader::upload(request('img_4'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    "public_id" => $public_id."-4"
                ));
            }else{
                $img_4 = array(
                    'secure_url' => ''
                );
            }

            if(request('img_5') != '' || request('img_5') != null){
                $validator=\Validator::make($request->all(),[
                    'img_5' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload an image 5
                $img_5 = \Cloudinary\Uploader::upload(request('img_5'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    "public_id" => $public_id."-5"
                ));
            }else{
                $img_5 = array(
                    'secure_url' => ''
                );
            }

            $product = Product::create([
                'name' => request('name'),
                'description' => request('description'),
                'color' => request('color'),
                'color_code' => request('color_code'),
                'how_to_use' => request('how_to_use'),
                'benefits' => request('benefits'),
                'variant_id' => $variant_id,
                'language_id' => $this->language,
                'tracking' => null,
                'image1' => $img_1['secure_url'],
                'image2' => $img_2['secure_url'],
                'image3' => $img_3['secure_url'],
                'image4' => $img_4['secure_url'],
                'image5' => $img_5['secure_url'],
                'public_id' => $public_id,
                'state_id' => request('state_id'),
            ]);

            if(request('discount') != 0){
                if(request('discount') >= 2){
                    $discount = 1;
                }else{
                    $discount = request('discount');
                }
                if($this->country == 2){
                    $price = request('price');
                    $price_discount = bcdiv(($price * $discount), "1", 2);

                    $final_price_not_roud = $price - $price_discount;

                    $final_price = bcdiv ($final_price_not_roud , "1" , 2);
                }else{
                    $price = request('price');
                    $price_discount = ($price * $discount);

                    $final_price = $price - $price_discount;
                }

                #return response()->json(['response' => [$final_price, $price, $discount, $price_discount, ceil($final_price)]], 200);
            }else{
                $discount = 0;
                $final_price = request('price');
            }

                $price_variant_validator = VariantPrice::where('country_id', $this->language)->where('variant_id', $variant_id)->first();
                if(!$price_variant_validator){
                    $price_variant = VariantPrice::create([
                        'price'=> request('price'),
                        'discount'=> $discount,
                        'final_price'=> $final_price,
                        'country_id'=> $this->country,
                        'variant_id'=> $variant_id
                    ]);
                }


            if(!$product){
                # If there is a problem delete the cloud photos
                $api = new \Cloudinary\Api();
                $api->delete_resources(array($img_1['public_id']));
                $api->delete_resources(array($img_2['public_id']));
                $api->delete_resources(array($img_3['public_id']));
                $api->delete_resources(array($img_4['public_id']));
                $api->delete_resources(array($img_5['public_id']));
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['response' => ['error' => $e->getMessage(), $e->getLine()]], 400);
        }

        DB::commit();
        return response()->json(['response' => 'Success'], 200);

    }

    public function variantList($id)
    {
        $variants = ProductVariant::select('product_variants.principal_id as principal_id', 'product_variants.name', 'product_variants.color_code', 'product_variants.color', 'product_variants.state_id', 'vap.discount', 'vap.final_price', 'vap.price', 'product_variants.quantity', 'product_variants.state_id as variant_state_id',
        'product_variants.favorite', 'product_variants.new_product', 'cruelty_free', 'product_variants.category1_order', 'product_variants.category2_order', 'product_variants.category3_order', 'product_variants.id')
        ->join('variant_price as vap', 'product_variants.id', 'vap.variant_id')
        ->join('products as p', 'product_variants.id', 'p.variant_id')
        ->name(request('name'))
        ->state(request('state'))
        ->category3(request('category3'))
        ->color(request('color'))
        ->where('product_variants.principal_id', $id)
        ->where('vap.country_id', $this->country)
        ->where('p.language_id', $this->language)
        ->get();

        return response()->json(['response' => $variants], 200);

    }

    public function showMaster($id)
    {
        $m_product = MProduct::find($id);

        return response()->json(['response' => $m_product], 200);

    }

    public function variantListCategory(Request $request)
    {

        # Join con la tabla de relación de precios país

        $products = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code',
        'products.benefits', 'products.how_to_use', 'products.variant_id', 'products.language_id', 'products.tracking', 'products.image1', 'products.image2',
        'products.image3', 'products.image4', 'vap.discount', 'vap.final_price',
        'products.image5', 'products.state_id', 'products.created_at', 'products.updated_at', 'vap.price', 'vp.quantity', 'vp.state_id as variant_state_id',
        'vp.favorite', 'vp.new_product', 'cruelty_free', 'vp.category1_order', 'vp.category2_order', 'vp.category3_order')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        ->join('variant_price as vap', 'vp.id', 'vap.variant_id')
        #->vState(request('v_state'))
        ->variantName(request('name'))
        ->category1(request('category1_id'))
        ->category2(request('category2_id'))
        ->category3(request('category3_id'))
        ->discount(request('discount'))
        ->language($this->language)
        ->where('vap.country_id', $this->country)
        ->paginate(8);

        $count = Product::select('vp.principal_id as principal_id')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        ->join('variant_price as vap', 'vp.id', 'vap.variant_id')
        #->vState(request('v_state'))
        ->language($this->language)
        ->variantName(request('name'))
        ->category1(request('category1_id'))
        ->category2(request('category2_id'))
        ->category3(request('category3_id'))
        ->where('vap.country_id', $this->country)
        ->count();

        return response()->json(['response' => $products, 'count' => $count], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        # Join con la tabla de relación de precios país

        $product = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color',
        'products.color_code', 'products.variant_id', 'products.language_id', 'products.benefits', 'products.how_to_use',
        'products.tracking', 'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5', 'products.state_id', 'products.created_at', 'products.updated_at',
        'vap.price', 'vp.quantity', 'vp.state_id as variant_state_id', 'vp.favorite', 'vp.new_product', 'cruelty_free', 'vap.discount', 'vap.final_price')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('variant_price as vap', 'vp.id', 'vap.variant_id')
        ->language($this->language)
        ->where('vap.country_id', $this->country)
        ->where('vp.id', $id)
        ->first();

        return response()->json(['response' => $product], 200);
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
            'name' => 'required|min:1|max:75|unique:product_variants,id',
            'description' => 'required',
            'color_code' => 'bail',
            'color' => 'bail',
            'benefits' => 'required',
            'how_to_use' => 'required',
            'principal_id' => 'required|integer|exists:m_products,id',
            'quantity' => 'required|integer',
            'price' => 'required|regex:'.$regex,
            'discount' => 'required|regex:'.$regex,
            'state_id' => 'required|integer|min:1|max:2',
            'variant_state' => 'required|integer|min:1|max:2',
            'change_img_1' => 'required|boolean',
            'change_img_2' => 'required|boolean',
            'change_img_3' => 'required|boolean',
            'change_img_4' => 'required|boolean',
            'change_img_5' => 'required|boolean',
            'favorite' => 'required|integer|min:0|max:1',
            'new_product' => 'required|integer|min:0|max:1',
            'cruelty_free' => 'required|integer|min:0|max:1',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $variant = ProductVariant::find($id);

        if(!$variant){
            return response()->json(['response' => ['error' => 'Variante de producto no encontrada']], 400);
        }

        $variant_language = Product::where('variant_id', $id)->where('language_id', $this->language)->first();

        if(!$variant_language){
            return response()->json(['response' => ['error' => 'Esta variante no tiene registro para el idioma seleccionado']], 400);
        }
        $language = Language::find($this->language);
        $previous_data = array(
            'name' => $variant_language->name,
            'description' => $variant_language->description,
            'color_code' => $variant->color_code,
            'color' => $variant->color,
            'benefits' => $variant->benefits,
            'how_to_use' => $variant->how_to_use,
            'principal_id' => $variant->principal_id,
            'quantity' => $variant->quantity,
            'price' => $variant->price,
            'variant_state_id' => $variant->state_id,
            'favorite' => $variant->favorite,
            'new_product' => $variant->new_product,
            'cruelty_free' => $variant->cruelty_free,
            'state_id' => $variant_language->state_id,
            'image1' => $variant_language->image1,
            'image2' => $variant_language->image2,
            'image3' => $variant_language->image3,
            'image4' => $variant_language->image4,
            'image5' => $variant_language->image5,
        );
        DB::beginTransaction();
        try {
            if($this->language == 1){
                $variant->name = request('name');
                $variant->update();
            }
            $variant->color_code = request('color_code');
            $variant->color = request('color');
            $variant->principal_id = request('principal_id');
            $variant->quantity = request('quantity');
            $variant->price = request('price');
            if(request('discount') != 0){
                if(request('discount') >= 2){
                    $discount = 1;
                }else{
                    $discount = request('discount');
                }
                if($this->country == 2){
                    $price = request('price');
                    $price_discount = bcdiv(($price * $discount), "1", 2);

                    $final_price_not_roud = $price - $price_discount;

                    $final_price = bcdiv ($final_price_not_roud , "1" , 2);
                }else{
                    $price = request('price');
                    $price_discount = ($price * $discount);

                    $final_price = $price - $price_discount;
                }

            }else{
                $discount = 0;
                $final_price = request('price');
            }
            $variant->discount = $discount;
            $variant->final_price = $final_price;
            $variant->state_id = request('variant_state');
            $variant->new_product = request('new_product');
            $variant->favorite = request('favorite');
            $variant->cruelty_free = request('cruelty_free');
            $variant->update();

            # Guardar aquí en la nueva tabla la relacion con precio y país
            $price_variant_validator = VariantPrice::where('country_id', $this->language)->where('variant_id', $variant->id)->first();
            if($price_variant_validator){
                $price_variant_validator->price = request('price');
                $price_variant_validator->discount = $discount;
                $price_variant_validator->final_price = $final_price;
                $price_variant_validator->update();
            }

            $variant_language->name = request('name');
            $variant_language->description = request('description');
            $variant_language->color = request('color');
            $variant_language->color_code = request('color_code');
            $variant_language->benefits = request('benefits');
            $variant_language->how_to_use = request('how_to_use');
            $variant_language->state_id = request('state_id');



            if(request('change_img_1')){
                $validator=\Validator::make($request->all(),[
                    'img_1' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_1'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $variant_language->public_id.'-1',
                    "invalidate"=> true
                ));

                $variant_language->image1 = $upload['secure_url'];
            }

            if(request('change_img_2')){
                $validator=\Validator::make($request->all(),[
                    'img_2' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_2'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $variant_language->public_id.'-2',
                    "invalidate"=> true
                ));

                $variant_language->image2 = $upload['secure_url'];
            }

            if(request('change_img_3')){
                $validator=\Validator::make($request->all(),[
                    'img_3' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_3'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $variant_language->public_id.'-3',
                    "invalidate"=> true
                ));

                $variant_language->image3 = $upload['secure_url'];
            }

            if(request('change_img_4')){
                $validator=\Validator::make($request->all(),[
                    'img_4' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_4'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $variant_language->public_id.'-4',
                    "invalidate"=> true
                ));

                $variant_language->image4 = $upload['secure_url'];
            }

            if(request('change_img_5')){
                $validator=\Validator::make($request->all(),[
                    'img_5' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_5'),
                array(
                    "folder" => "MOS/products/".$language->name,
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $variant_language->public_id.'-5',
                    "invalidate"=> true
                ));

                $variant_language->image5 = $upload['secure_url'];
            }

            $new_data = array(
                'name' => $variant_language->name,
                'description' => $variant_language->description,
                'color_code' => $variant->color_code,
                'color' => $variant->color,
                'benefits' => $variant->benefits,
                'how_to_use' => $variant->how_to_use,
                'principal_id' => $variant->principal_id,
                'quantity' => $variant->quantity,
                'price' => $variant->price,
                'discount' => $variant->discount,
                'final_price' => $variant->final_price,
                'variant_state_id' => $variant->state_id,
                'favorite' => $variant->favorite,
                'new_product' => $variant->new_product,
                'cruelty_free' => $variant->cruelty_free,
                'state_id' => $variant_language->state_id,
                'image1' => $variant_language->image1,
                'image2' => $variant_language->image2,
                'image3' => $variant_language->image3,
                'image4' => $variant_language->image4,
                'image5' => $variant_language->image5,
            );
            $tracking = array(
                'previous_data' => $previous_data,
                'new_data' => $new_data,
            );
            if($variant_language->tracking == null){
                $last_tracking = array();
                array_push($last_tracking, $tracking);
                $variant_language->tracking = json_encode($last_tracking);

            }else{
                $last_tracking = json_decode($variant_language->tracking);
                array_push($last_tracking, $tracking);
                $variant_language->tracking = json_encode($last_tracking);
            }
            $variant_language->update();

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['response' => ['error' => $e->getMessage(), $e->getLine()]], 400);
        }

        DB::commit();
        return response()->json(['response' => 'Success'], 200);

    }

    public function updateMaster(Request $request, $id)
    {
        $validator=\Validator::make($request->all(),[
            'name' => 'required|min:1|max:75|unique:m_products,id',
            'state_id' => 'required|integer|min:0|max:1',
            'category1_id' => 'integer',
            'category2_id' => 'integer',
            'category3_id' => 'integer',
            'brand_id' => 'required|integer|exists:brands,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $master = MProduct::find($id);

        if(!$master){
            return response()->json(['response' => ['error' => 'Producto no encontrado'], 400]);
        }

        $category_3 = MCategory3::find(request('category3_id'));
        $category_2 = MCategory2::find(request('category2_id'));
        $category_1 = MCategory1::find(request('category1_id'));

        if($category_1){
            $category1 = $category_1->id;
        }else{
            $category1 = null;
        }

        if($category_2){
            $category2 = $category_2->id;
        }else{
            $category2 = null;
        }

        if($category_3){
            $category3 = $category_3->id;
        }else{
            $category3 = null;
        }

        $master->name = request('name');
        $master->category3_id = $category3;
        $master->category2_id = $category2;
        $master->category1_id = $category1;
        $master->brand_id = request('brand_id');
        $master->state_id = request('state_id');
        $master->update();

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

    /**
     * Ordenar productos por categorías
     */
    public function orderProducts(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'variant_id' => 'required|exists:product_variants,id',
            'category1_id' => 'bail|integer',
            'category2_id' => 'bail|integer',
            'category3_id' => 'bail|integer',
            'order' => 'bail|integer|required',

        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        if(!empty(request('category1_id'))){
            $category = MCategory1::find(request('category1_id'));
            if(!$category){
                return response()->json(['response' => ['error' => 'Categoría no encontrada']]);
            }

            $product = ProductVariant::select('mp.id as principal_id', 'product_variants.id as variant_id', 'product_variants.category1_order')
            ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
            ->where('mp.category1_id', request('category1_id'))
            ->where('product_variants.id', request('variant_id'))
            ->first();

            if(!$product){
                return response()->json(['response' => ['error' => 'Variante de el producto no encontrada o no pertenece a esta categoría']]);
            }

            $last_order = ProductVariant::select('product_variants.id', 'product_variants.category1_order')
            ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
            ->where('product_variants.category1_order', request('order'))
            ->where('mp.category1_id', request('category1_id'))
            ->first();


            if($last_order){
                $product = ProductVariant::find(request('variant_id'));

                $last_order->category1_order = $product->category1_order;
                $product->category1_order = request('order');

                $last_order->update();
                $product->update();

            }else{

                $max = ProductVariant::select('product_variants.id', 'product_variants.category1_order')
                ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                ->where('mp.category1_id', request('category1_id'))
                ->max('product_variants.category1_order');

                if(request('order') >= $max){

                    $max_variant = ProductVariant::select('product_variants.id', 'product_variants.category1_order')
                    ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                    ->where('mp.category1_id', request('category1_id'))
                    ->where('product_variants.category1_order', '>=', $max)
                    ->first();

                    $product = ProductVariant::find(request('variant_id'));

                    $value = $max_variant->category1_order;

                    $max_variant->category1_order = $product->category1_order;
                    $product->category1_order = $value;
                    $max_variant->update();
                    $product->update();
                }else if(request('order') < $max){
                    $product = ProductVariant::find(request('variant_id'));

                    $product->category1_order = request('order');
                    $product->update();
                }
            }
            return response()->json(['response' => 'Succes'], 200);


        }else if(!empty(request('category2_id'))){

            $category = MCategory2::find(request('category2_id'));
            if(!$category){
                return response()->json(['response' => ['error' => 'Categoría no encontrada']]);
            }

            $product = ProductVariant::select('mp.id as principal_id', 'product_variants.id as variant_id', 'product_variants.category2_order')
            ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
            ->where('mp.category2_id', request('category2_id'))
            ->where('product_variants.id', request('variant_id'))
            ->first();

            if(!$product){
                return response()->json(['response' => ['error' => 'Variante de el producto no encontrada o no pertenece a esta categoría']]);
            }

            $last_order = ProductVariant::select('product_variants.id', 'product_variants.category2_order')
            ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
            ->where('product_variants.category2_order', request('order'))
            ->where('mp.category2_id', request('category2_id'))
            ->first();


            if($last_order){
                $product = ProductVariant::find(request('variant_id'));

                $last_order->category2_order = $product->category2_order;
                $product->category2_order = request('order');

                $last_order->update();
                $product->update();

            }else{

                $max = ProductVariant::select('product_variants.id', 'product_variants.category2_order')
                ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                ->where('mp.category2_id', request('category2_id'))
                ->max('product_variants.category2_order');

                if(request('order') >= $max){

                    $max_variant = ProductVariant::select('product_variants.id', 'product_variants.category2_order')
                    ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                    ->where('mp.category2_id', request('category2_id'))
                    ->where('product_variants.category2_order', '>=', $max)
                    ->first();

                    $product = ProductVariant::find(request('variant_id'));

                    $value = $max_variant->category2_order;

                    $max_variant->category2_order = $product->category2_order;
                    $product->category2_order = $value;
                    $max_variant->update();
                    $product->update();
                }else if(request('order') < $max){
                    $product = ProductVariant::find(request('variant_id'));

                    $product->category2_order = request('order');
                    $product->update();
                }
            }
            return response()->json(['response' => 'Succes'], 200);

        }else if(!empty(request('category3_id'))){

            $category = MCategory3::find(request('category3_id'));
            if(!$category){
                return response()->json(['response' => ['error' => 'Categoría no encontrada']]);
            }

            $product = ProductVariant::select('mp.id as principal_id', 'product_variants.id as variant_id', 'product_variants.category3_order')
            ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
            ->where('mp.category3_id', request('category3_id'))
            ->where('product_variants.id', request('variant_id'))
            ->first();

            if(!$product){
                return response()->json(['response' => ['error' => 'Variante de el producto no encontrada o no pertenece a esta categoría']]);
            }

            $last_order = ProductVariant::select('product_variants.id', 'product_variants.category3_order')
            ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
            ->where('product_variants.category3_order', request('order'))
            ->where('mp.category3_id', request('category3_id'))
            ->first();


            if($last_order){
                $product = ProductVariant::find(request('variant_id'));

                $last_order->category3_order = $product->category3_order;
                $product->category3_order = request('order');

                $last_order->update();
                $product->update();

            }else{

                $max = ProductVariant::select('product_variants.id', 'product_variants.category3_order')
                ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                ->where('mp.category3_id', request('category3_id'))
                ->max('product_variants.category3_order');

                if(request('order') >= $max){

                    $max_variant = ProductVariant::select('product_variants.id', 'product_variants.category3_order')
                    ->join('m_products as mp', 'product_variants.principal_id', 'mp.id')
                    ->where('mp.category3_id', request('category3_id'))
                    ->where('product_variants.category3_order', '>=', $max)
                    ->first();

                    $product = ProductVariant::find(request('variant_id'));

                    $value = $max_variant->category3_order;

                    $max_variant->category3_order = $product->category3_order;
                    $product->category3_order = $value;
                    $max_variant->update();
                    $product->update();
                }else if(request('order') < $max){
                    $product = ProductVariant::find(request('variant_id'));

                    $product->category3_order = request('order');
                    $product->update();
                }
            }
            return response()->json(['response' => 'Succes'], 200);

        }

    }
}
