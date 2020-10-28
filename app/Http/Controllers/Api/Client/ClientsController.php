<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerByCategory;
use App\Models\Brand;
use App\Models\Category1;
use App\Models\Category2;
use App\Models\Category3;
use App\Models\Language;
use App\Models\Product;
use App\Models\ClientEmail;
use App\Models\VideoHome;
use Illuminate\Http\Request;

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
        ->where('products.state_id', 1)
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


        return response()->json(['response' => $products, 'brands' => $all_brands, 'min' => $min, 'max' => $max], 200);
    }

    public function minAndMax(Request $request)
    {
        $brands = Product::select('mp.brand_id', 'vp.price')
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
        ->where('products.state_id', 1)
        ->language($this->language)
        ->get();

        $brands_collect = collect($brands)->pluck('brand_id');

        $min = collect($brands)->min('price');
        $max = collect($brands)->max('price');


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
        ->where('products.state_id', 1)
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
        ->where('products.state_id', 1)
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
        ->where('products.state_id', 1)
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
        ->where('products.state_id', 1)
        ->language($this->language)
        ->get();

        $product->categories_1 = $cat1;
        $product->categories_2 = $cat2;
        $product->categories_3 = $cat3;

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
        ->where('products.state_id', 1)
        ->language($this->language)
        ->get();

        $product->colors = $colors;

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
        $cities = City::select('name', 'department_name', 'delivery_fee', 'delivery_time')
        ->where('state', 1)
        ->get();

        return response()->json(['response' => $cities], 200);
    }
}
