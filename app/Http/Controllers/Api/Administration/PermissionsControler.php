<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionsControler extends Controller
{
    public function index()
    {
        $permissions = DB::table('permission')->select('id', 'name', 'description', 'module_id')->get();

        return response()->json(['response' => $permissions], 200);
    }
}
