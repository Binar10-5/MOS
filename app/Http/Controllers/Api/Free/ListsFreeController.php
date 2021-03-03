<?php

namespace App\Http\Controllers\Api\Free;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
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
}
