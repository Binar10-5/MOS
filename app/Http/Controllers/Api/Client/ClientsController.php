<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category1;
use App\Models\Category2;
use App\Models\Category3;
use App\Models\Language;
use App\Models\Product;
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
        $categories_1 = Category1::select('mc1.name', 'mc1.id as principal_id', 'mc1.state_id as entity_state_id')
        ->join('m_categories_1 as mc1', 'categories_1.principal_id', 'mc1.id')
        ->language($this->language)
        ->get();

        foreach ($categories_1 as $c1) {
            $categories_2 = Category2::select('mc2.id as principal_id', 'mc2.name', 'mc2.state_id as entity_state_id')
            ->join('m_categories_2 as mc2', 'categories_2.principal_id', 'mc2.id')
            ->category1($c1->principal_id)
            ->language($this->language)
            ->get();

            $c1->categories_2 = $categories_2;
            foreach ($categories_2 as $c2) {
                $categories_3 = Category3::select('mc3.id as principal_id', 'mc3.name', 'mc3.state_id as entity_state_id')
                ->join('m_categories_3 as mc3', 'categories_3.principal_id', 'mc3.id')
                ->category2($c2->principal_id)
                ->language($this->language)
                ->get();

                $c2->categories_3 = $categories_3;
            }
        }

        return response()->json(['response' => $categories_1], 200);
    }

    public function productsList()
    {
        $products = Product::select('vp.principal_id as principal_id', 'products.name', 'products.description', 'products.color', 'products.color_code', 'products.variant_id', 'products.language_id',
        'products.tracking', 'products.image1', 'products.image2', 'products.image3', 'products.image4', 'products.image5', 'products.state_id', 'products.created_at', 'products.updated_at', 'vp.price', 'vp.quantity', 'vp.state_id as variant_state_id',
        'vp.category1_order', 'vp.category2_order', 'vp.category3_order', 'vp.new_product')
        ->join('product_variants as vp', 'products.variant_id', 'vp.id')
        ->join('m_products as mp', 'vp.principal_id', 'mp.id')
        ->join('m_categories_1 as mc1', 'mp.category1_id', 'mc1.id')
        ->join('m_categories_2 as mc2', 'mp.category2_id', 'mc2.id')
        ->join('m_categories_3 as mc3', 'mp.category3_id', 'mc3.id')
        #->vState(request('v_state'))
        ->category1(request('category1_id'))
        ->category2(request('category2_id'))
        ->category3(request('category3_id'))
        ->languageName(request('name'))
        ->where('mp.state_id', 1)
        ->where('products.state_id', 1)
        ->language($this->language)
        ->paginate(8);

        return response()->json(['response' => $products], 200);
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
}
