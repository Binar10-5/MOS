<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerByCategory;
use App\Models\Brand;
use App\Models\Category1;
use App\Models\Category2;
use App\Models\Category3;
use App\Models\City;
use App\Models\Language;
use App\Models\Product;
use App\Models\ClientEmail;
use App\Models\Cupon;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\OrderState;
use App\Models\PqrsClients;
use App\Models\ProductVariant;
use App\Models\Tutorial;
use App\Models\VideoHome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Helpers\SendEmails;
use Exception;

class ClientsController extends Controller
{
    public function __construct(Request $request)
    {
        // Get the languaje id
        $language = Language::find($request->header('language-key'));
        if($language){
            $this->language = $request->header('language-key');
        }else{
            $this->language = 1;
        }
    }

    public function categoriesList()
    {
        $categories_1 = Category1::select('categories_1.name', 'mc1.id as principal_id', 'mc1.state_id as entity_state_id')
        ->join('m_categories_1 as mc1', 'categories_1.principal_id', 'mc1.id')
        ->language($this->language)
        ->where('mc1.state_id', 1)
        ->get();

        foreach ($categories_1 as $c1) {
            $categories_2 = Category2::select('mc2.id as principal_id', 'categories_2.name', 'mc2.state_id as entity_state_id', 'categories_2.image')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            ->category1($c1->principal_id)
            ->language($this->language)
            ->where('mc2.state_id', 1)
            ->get();

            $c1->categories_2 = $categories_2;
            foreach ($categories_2 as $c2) {
                $categories_3 = Category3::select('mc3.id as principal_id', 'categories_3.name', 'mc3.state_id as entity_state_id')
                ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
                ->category2($c2->principal_id)
                ->language($this->language)
                ->where('mc3.state_id', 1)
                ->get();

                $c2->categories_3 = $categories_3;
            }
        }

        return response()->json(['response' => $categories_1], 200);
    }

    public function categories2List($id)
    {
        $category = Category1::select('categories_1.name', 'mc1.id as principal_id', 'mc1.state_id as entity_state_id')
        ->join('m_categories_1 as mc1', 'categories_1.principal_id', 'mc1.id')
        ->language($this->language)
        ->where('mc1.state_id', 1)
        ->where('mc1.id', $id)
        ->first();

        if(!$category){
            return response()->json(['response' => ['error' => ['Categoría no encontrada']]], 400);
        }

        $categories_2 = Category2::select('mc2.id as principal_id', 'categories_2.name', 'mc2.state_id as entity_state_id', 'categories_2.image')
        ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
        ->category1($category->principal_id)
        ->language($this->language)
        ->where('mc2.state_id', 1)
        ->get();

        $category->categories_2 = $categories_2;
        foreach ($categories_2 as $c2) {
            $categories_3 = Category3::select('mc3.id as principal_id', 'categories_3.name', 'mc3.state_id as entity_state_id')
            ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
            ->category2($c2->principal_id)
            ->language($this->language)
            ->where('mc3.state_id', 1)
            ->get();

            $c2->categories_3 = $categories_3;
        }

        return response()->json(['response' => $category], 200);
    }

    public function productsList()
    {
        $products = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
        'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5', 'products.benefits', 'products.how_to_use',
        'products.state_id', 'products.updated_at', 'vp.price', 'vp.quantity', 'vp.state_id as variant_state_id',
        'vp.category1_order', 'vp.category2_order', 'vp.category3_order', 'vp.new_product', 'vp.favorite', 'vp.new_product', 'vp.cruelty_free', 'vp.discount', 'vp.final_price')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        #->vState(request('v_state'))
        ->category1(request('category1_id'))
        ->category2(request('category2_id'))
        ->category3(request('category3_id'))
        ->favorite(request('favorite'))
        ->newProduct(request('new_product'))
        ->priceRange(request('min'), request('max'))
        ->discount(request('discount'))
        ->brand(json_decode(request('brands')))
        ->languageName(request('name'))
        ->where('mp.state_id', 1)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->paginate(8);

        $brands = Product::select('mp.brand_id', 'vp.price')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        #->vState(request('v_state'))
        ->category1(request('category1_id'))
        ->category2(request('category2_id'))
        ->category3(request('category3_id'))
        ->favorite(request('favorite'))
        ->newProduct(request('new_product'))
        ->priceRange(request('min'), request('max'))
        ->languageName(request('name'))
        ->where('mp.state_id', 1)
        ->where('vp.state_id', 1)
        ->where('products.state_id', 1)
        ->language($this->language)
        ->get();

        $brands_collect = collect($brands)->pluck('brand_id');

        $min = collect($brands)->min('price');
        $max = collect($brands)->max('price');


        $brands_count = collect($brands_collect)->countBy()->sortKeys();

        $all_brands = Brand::whereIn('id', $brands_collect)->get();

        foreach ($all_brands as $brand) {
            $brand->count = $brands_count[$brand->id];
        }


    return response()->json(['response' => $products, 'brands' => $all_brands/*, 'min' => $min, 'max' => $max*/], 200);
    }

    public function minAndMax(Request $request)
    {
        $brands = Product::select('mp.brand_id', 'vp.final_price')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        #->vState(request('v_state'))
        ->category1(request('category1_id'))
        ->priceRange(request('min'), request('max'))
        ->favorite(request('favorite'))
        ->newProduct(request('new_product'))
        ->brand(json_decode(request('brands')))
        ->discount(request('discount'))
        ->where('mp.state_id', 1)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->get();

        $brands_collect = collect($brands)->pluck('brand_id');

        $min = collect($brands)->min('final_price');
        $max = collect($brands)->max('final_price');


        return response()->json(['min' => $min, 'max' => $max], 200);

    }

    public function productsListDetail($id)
    {
        $product = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
        'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5', 'products.state_id', 'products.benefits', 'products.how_to_use',
        'vp.price', 'vp.quantity', 'vp.state_id as variant_state_id',
        'vp.category1_order', 'vp.category2_order', 'vp.category3_order', 'vp.new_product', 'vp.favorite', 'vp.new_product', 'vp.cruelty_free', 'vp.discount', 'vp.final_price')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        #->vState(request('v_state'))
        ->where('mp.state_id', 1)
        ->where('vp.id', $id)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->first();

        $cat1 = Product::select('mc1.id', 'c1.name', 'c1.description')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('categories_1 as c1', 'mc1.id', 'c1.principal_id')
        #->vState(request('v_state'))
        ->where('mc1.state_id', 1)
        ->where('vp.id', $id)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->get();

        $cat2 = Product::select('mc2.id', 'c2.name', 'c2.description')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        ->join('categories_2 as c2', 'mc2.id', 'c2.principal_id')
        #->vState(request('v_state'))
        ->where('mc2.state_id', 1)
        ->where('vp.id', $id)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->get();

        $cat3 = Product::select('mc3.id', 'c3.name', 'c3.description')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_3 as mc3', 'mp.category3_id', 'mc3.id')
        ->join('categories_3 as c3', 'mc3.id', 'c3.principal_id')
        #->vState(request('v_state'))
        ->where('mc3.state_id', 1)
        ->where('vp.id', $id)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->get();

        if($product){
            $product->categories_1 = $cat1;
            $product->categories_2 = $cat2;
            $product->categories_3 = $cat3;
        }

        if(!$product){
            return response()->json(['response' => ['error' => 'Producto no encontrado']], 400);
        }

        $colors = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
        'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5', 'products.state_id', 'products.benefits', 'products.how_to_use',
        'vp.price', 'vp.quantity', 'vp.state_id as variant_state_id',
        'vp.category1_order', 'vp.category2_order', 'vp.category3_order', 'vp.new_product', 'vp.favorite', 'vp.new_product', 'vp.cruelty_free')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        #->vState(request('v_state'))
        ->where('mp.state_id', 1)
        ->where('mp.id', $product->principal_id)
        ->where('vp.state_id', 1)
        ->language($this->language)
        ->get();

        if($product){
            $product->colors = $colors;
        }

        return response()->json(['response' => $product], 200);
    }

    public function brandsList()
    {
        $brands = Brand::where('state', 1)
        ->get();

        return response()->json(['response' => $brands], 200);
    }

    public function bannersList()
    {
        $banners = Banner::select('banner.id as son_id', 'banner.name', 'banner.description', 'banner.img_short', 'banner.img_median', 'banner.img_big', 'banner.link', 'mb.state_id as entity_state_id', 'banner.public_id',
        'banner.order_by', 'banner.language_id', 'banner.principal_id', 'banner.state_id')
        ->join('m_banners as mb', 'banner.principal_id', 'mb.id')
        ->state(1)
        ->where('banner.state_id', 1)
        ->orderBy('order_by', 'ASC')
        ->where('banner.language_id', $this->language)
        ->get();

        return response()->json(['response' => $banners], 200);

    }

    public function bannersByCatgoryList($id)
    {
        $banner = BannerByCategory::select('banners_by_category.id as son_id', 'banners_by_category.name', 'banners_by_category.description', 'banners_by_category.img_short', 'banners_by_category.img_median', 'banners_by_category.img_big', 'banners_by_category.link', 'mb.state as entity_state_id', 'banners_by_category.public_id',
        'banners_by_category.order_by', 'banners_by_category.language_id', 'banners_by_category.principal_id', 'banners_by_category.state_id')
        ->join('m_banners_by_category as mb', 'banners_by_category.principal_id', 'mb.id')
        ->where('banners_by_category.language_id', $this->language)
        ->where('mb.id', $id)
        ->first();

        return response()->json(['response' => $banner], 200);

    }

    public function videoHomeList()
    {
        $videos = VideoHome::select('video_home.id as son_id', 'video_home.name', 'video_home.description', 'video_home.video', 'video_home.principal_id', 'video_home.language_id', 'mv.state')
        ->join('m_video_home as mv', 'video_home.principal_id', 'mv.id')
        ->where('video_home.language_id', $this->language)
        ->where('mv.state', 1)
        ->get();

        return response()->json(['response' => $videos], 200);

    }

    public function clientEmail(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'email' => 'required|max:80|email',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }
        $valid_email = ClientEmail::where('email', request('email'))->first();

        if($valid_email){
            return response()->json(['response' => ['error' => ['El correo ya se encuentra registrado']]], 400);
        }

        if(!$valid_email){
            $client_email = ClientEmail::create([
                'email' => request('email'),
                'state' => 1,
            ]);
        }

        return response()->json(['response' => 'Success'], 200);
    }

    public function citiesList(Request $request)
    {
        $cities = City::select('id', 'name', 'department_name', 'delivery_fee', 'delivery_time')
        ->where('state', 1)
        ->get();

        return response()->json(['response' => $cities], 200);
    }

    public function validateCupon(Request $request, $code)
    {
        $cupon = Cupon::where('state', 1)->where('code', $code)->first();

        if(!$cupon){
            return response()->json(['response' => ['error' => ['El cupón no existe o está inactivo']]], 400);
        }

        if($cupon->uses_number > $cupon->maximum_uses){
            return response()->json(['response' => ['error' => ['El cupón ya alcanzó el máximo de usos']]], 400);
        }

        return response()->json(['response' => $cupon], 200);
    }

    public function tutorialsList(Request $request)
    {
        if(request('paginate')){
            $tutorials = Tutorial::select('tutorials.title', 'mt.id as principal_id', 'mt.state', 'tutorials.description', 'tutorials.image', 'mt.created_at', 'mt.updated_at')
            ->join('m_tutorials as mt', 'tutorials.principal_id', 'mt.id')
            ->name(request('title'))
            ->where('mt.state', 1)
            ->language($this->language)
            ->orderBy('mt.created_at', 'desc')
            ->paginate(8);
        }else{
            $tutorials = Tutorial::select('tutorials.title', 'mt.id as principal_id', 'mt.state', 'tutorials.description', 'tutorials.image', 'mt.created_at', 'mt.updated_at')
            ->join('m_tutorials as mt', 'tutorials.principal_id', 'mt.id')
            ->state(request('state'))
            ->where('mt.state', 1)
            ->language($this->language)
            ->orderBy('mt.created_at', 'desc')
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

    public function tutorialsDetail(Request $request, $id)
    {
        $tutorial = Tutorial::select('tutorials.title', 'mt.id as principal_id', 'mt.state', 'tutorials.description', 'tutorials.image', 'tutorials.content', 'tutorials.slider',
        'tutorials.principal_id', 'tutorials.language_id', 'tutorials.state')
        ->join('m_tutorials as mt', 'tutorials.principal_id', 'mt.id')
        ->where('mt.state', 1)
        ->language($this->language)
        ->where('mt.id', $id)
        ->first();

        if($tutorial){
            $products = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
            'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5',
            'products.state_id', 'vp.new_product', 'vp.favorite', 'vp.cruelty_free', 'vp.discount', 'vp.price', 'vp.final_price')
            ->join('product_variants as vp', 'products.variant_id', 'vp.id')
            ->join('tutorial_products as tp', 'vp.id', 'tp.product_id')
            ->where('tp.tutorial_id', $tutorial->principal_id)
            ->language($this->language)
            ->get();

            $tutorial->products = $products;
        }

        return response()->json(['response' => $tutorial], 200);
    }

    public function requestOrder(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'client_name' => 'required',
            'client_dni' => 'required',
            'client_last_name' => 'required',
            'client_address' => 'bail|required',
            'client_cell_phone' => 'bail|required',
            'client_email' => 'bail|required',
            'products_list' => 'bail|required|array',
            'coupon' => 'bail',
            'city_id' => 'bail|required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $subtotal = 0;
        foreach (request('products_list') as $product) {

            $variant = ProductVariant::find($product['id']);

            if(!$variant){
                return response()->json(['response' => ['error' => ['La variante de el producto no existe', $product]]], 400);
            }


            if($variant->quantity < $product['quantity']){
                return response()->json(['response' => ['error' => ['Lo sentimos en el momento de efectuar la compra nos quedamos sin la existencia del productos solictado.', $variant]]], 400);
            }
            # MIRAR AQUÍ
            # Poner el final price en el precio
            $subtotal += (int)$variant->final_price * $product['quantity'];

        }
        DB::beginTransaction();
        try{
            $total = $subtotal;
            $coupon = null;
            if(!empty(request('coupon'))){
                $validate_coupon = Cupon::where('code', request('coupon'))->first();

                if(!$validate_coupon){
                    return response()->json(['response' => ['error' => ['El cupón no existe']]], 400);
                }

                if($validate_coupon->uses_number >= $validate_coupon->maximum_uses){
                    return response()->json(['response' => ['error' => ['El cupón ya alcanzó un limite de usos']]], 400);
                }

                if($validate_coupon->minimal_cost > $subtotal){
                    return response()->json(['response' => ['error' => ['El costo de el pedido tiene que ser mayor a '.$validate_coupon->minimal_cost.' para poder usar el cupón']]], 400);
                }

                /*$validate_coupon->uses_number += 1;
                $validate_coupon->update();*/
                $total -= $validate_coupon->discount_amount;
                $coupon = $validate_coupon->id;
            }

            // Si el sub total de la compra es mayor a 80, el delivery free de la ciudad es 0, osea que no se le suma a el total
            $city = City::find(request('city_id'));
            $delivery = DB::table('delivery_fee_minimum')->where('id', 1)->first();
            $delivery_fee = $city->delivery_fee;
            if($total < $delivery->delivery_fee){
                $total += (int)$delivery_fee;
            }else{
                $delivery_fee = 0;
            }
            $order_number = Order::orderBy('id', 'desc')->first();

            if(substr($order_number->order_number, 4) <= 0){
                $new_order_number = 100000;
            }else{
                $new_order_number = substr($order_number->order_number, 4) + 1;
            }

            $new_state = OrderState::find(1);

            $tracking = [array(
                'last_id' => 0,
                'last_state'=> 'No creado',
                'state_id'=> $new_state->id,
                'state'=> $new_state->name,
                'state_date'=> date('Y-m-d H:i:s'),
                'reason'=> ''
            )];


            $order = Order::create([
                'order_number' => 'dev-mos-'.$new_order_number,
                'client_name' => request('client_name'),
                'client_dni' => request('client_dni'),
                'client_last_name' => request('client_last_name'),
                'client_address' => request('client_address'),
                'client_cell_phone' => request('client_cell_phone'),
                'client_email' => request('client_email'),
                'subtotal' => $subtotal,
                'total' => $total,
                'delivery_fee' => $delivery_fee,
                'state_id' => 1,
                'tracking'=> json_encode($tracking),
                'coupon_id' => $coupon,
                'city_id' => request('city_id'),
                'language_id' => $this->language
            ]);


            $valid_array = array();
            $valid_data = array();
            foreach (request('products_list') as $product) {

                $variant = ProductVariant::find($product['id']);

                if(!$variant){
                    return response()->json(['response' => ['error' => ['La variante de el producto no existe', $product]]], 400);
                }



                if(!in_array($product['id'], $valid_array)){
                    array_push($valid_data, [
                        'order_id' => $order->id,
                        'name' => $variant->name,
                        'color' => $variant->color,
                        'color_code' => $variant->color_code,
                        'price' => $variant->price,
                        'discount' => $variant->discount,
                        'final_price' => $variant->final_price,
                        'total' => (int)$variant->final_price * $product['quantity'],
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                    ]);


                    /*$variant->quantity -= $product['quantity'];
                    $variant->update();*/
                }
                array_push($valid_array, $product['id']);
            }

            $order_products = OrderProducts::insert($valid_data);

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['response' => ['error' => [$e->getMessage()]]], 400);
        }

        DB::commit();
        return response()->json(['response' => $order->order_number, 'total' => $order->total], 200);
    }

    public function deliveryFeeClient()
    {
        $delivery = DB::table('delivery_fee_minimum')->where('id', 1)->first();

        return response()->json(['response' => $delivery], 200);

    }

    public function orderStateId()
    {
        $state = OrderState::get();

        return response()->json(['response' => $state], 200);

    }

    public function pqrsClient(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'name' => 'required|min:1|max:75',
            'last_name' => 'required|min:1|max:75',
            'email' => 'required|email',
            'cell_phone' => 'required|max:15',
            'pqrs_id' => 'required|exists:contact_type,id',
            'message' => 'required'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        # Find pqrs
        $pqrs = DB::table('contact_type')->find(request('pqrs_id'));

        # Data of the user who performs the pqrs
        $principal_email = array((object)['email' => request('email'), 'name' => request('name')." ".request('last_name')]);

        DB::beginTransaction();
        try{
            $new_client = PqrsClients::create([
                'name' => request('name'),
                'last_name' => request('last_name'),
                'email' => request('email'),
                'cell_phone' => request('cell_phone'),
            ]);

            $client_id = $new_client->id;

            # We generate the data to send the mail to the created user
            $data = array(
                'name' => request('name')." ".request('last_name'),
                'email' => request('email'),
                'pqrs' => $pqrs->name,
                'message' => request('message'),
                'pqrs_id' => $client_id
            );
            # Send email

            # Send Notification
            $mail = Mail::to(request('email'))->send(new SendEmails('pqrs_client', 'PQRS MOS.', 'noreply@mosbeautyshop.com', $data));

            if($mail){
                return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
            }

            /*$send_email = SendEmailHelper::sendEmail('Correo de pqrs.', TemplatesHelper::pqrsData($data), $principal_email, array());
            if($send_email != 1){
                return response()->json(['response' => ['error' => [$send_email]]], 400);
            }*/

            # We generate the data to send the mail to the created user
            $data_2 = array(
                'admin_name' => 'Admin PQRS',
                'name' => request('name')." ".request('last_name'),
                'email' => request('email'),
                'cell_phone' => request('cell_phone'),
                'subject' => $pqrs->name,
                'description' => $pqrs->description,
                'pqrs' => $pqrs->name,
                'pqrs_id' => $client_id,
                'message' => request('message')
            );
            $principal_email = array((object)['email' => 'myothersidebeauty@hotmail.com', 'name' => 'Atención a el cliente']);
            #$principal_email = array((object)['email' => 'programador5@binar10.co', 'name' => 'Atención a el cliente']);

            # Send Notification
            $mail = Mail::to('programador5@binar10.co')->send(new SendEmails('pqrs_admin', 'Nuevo pqrs # '.$client_id, 'noreply@mosbeautyshop.com', $data_2));

            if($mail){
                return response()->json(['response' => ['error' => ['Error al enviar el correo.']]], 400);
            }

            /*# Send email to admin
            $send_email = SendEmailHelper::sendEmail('Nuevo pqrs # '.$client_id, TemplatesHelper::pqrsDataAdmin($data_2), $principal_email, array());
            if($send_email != 1){
                return response()->json(['response' => ['error' => [$send_email]]], 400);
            }*/
        }catch(Exception $e){
            DB::rollback();
            return response()->json( ['response' => ['error' => ['Error al crear el cliente'], 'data' => [$e->getMessage(), $e->getFile(), $e->getLine()]]], 400);
        }

        DB::commit();
        return response()->json(['response' => 'Su solicitud a sido recibida, pronto estaremos en contacto con usted.'], 200);
    }

    public function pqrsType()
    {
        $pqrs = DB::table('contact_type')->get();

        return response()->json(['response' => $pqrs], 200);

    }

    public function validateProductExistence(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'products' => 'required|array'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $products = ProductVariant::whereIn('id', request('products'))->get();

        return response()->json(['response' => $products], 200);
    }

}
