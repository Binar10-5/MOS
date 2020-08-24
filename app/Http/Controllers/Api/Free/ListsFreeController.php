<?php

namespace App\Http\Controllers\Api\Free;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class ListsFreeController extends Controller
{
    public function language()
    {
        $language = Language::get();

        return response()->json(['response' => $language], 200);
    }
}
