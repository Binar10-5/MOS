<?php

namespace App\Http\Controllers\Api\Free;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Product;
use App\Models\VariantPrice;
use Illuminate\Http\Request;

class ListsFreeController extends Controller
{
    public function language()
    {
        $language = Language::where('state', 1)->get();

        return response()->json(['response' => $language], 200);
    }

    public function country()
    {
        $countries = Country::where('state', 1)->get();

        return response()->json(['response' => $countries], 200);
    }

    public function currency()
    {
        $currencies = Currency::where('state', 1)->get();

        return response()->json(['response' => $currencies], 200);
    }

    public function addNewPrices()
    {
        $prices = product::select('v.id', 'v.price', 'v.discount', 'v.final_price')
        ->join('product_variants as v', 'products.variant_id', 'v.id')
        ->where('products.language_id', 1)
        ->get();

        foreach ($prices as $price) {
            $validate_col = VariantPrice::where('variant_id', $price->id)->where('country_id', 1)->first();
            if(!$validate_col){
                $add_prices = VariantPrice::create([
                    'price'=> $price->price,
                    'discount'=> $price->discount,
                    'final_price'=> $price->final_price,
                    'country_id'=> 1,
                    'variant_id'=> $price->id,
                ]);
            }

        }

        $prices = product::select('v.id', 'v.price', 'v.discount', 'v.final_price')
        ->join('product_variants as v', 'products.variant_id', 'v.id')
        ->where('products.language_id', 2)
        ->get();

        foreach ($prices as $price) {
            $validate_col = VariantPrice::where('variant_id', $price->id)->where('country_id', 2)->first();
            if(!$validate_col){
                $add_prices = VariantPrice::create([
                    'price'=> $price->price,
                    'discount'=> $price->discount,
                    'final_price'=> $price->final_price,
                    'country_id'=> 2,
                    'variant_id'=> $price->id,
                ]);
            }

        }
    }
}
