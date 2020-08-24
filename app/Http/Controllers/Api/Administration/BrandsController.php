<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_brands')->only(['show', 'index']);
        $this->middleware('permission:/create_brand')->only(['store']);
        $this->middleware('permission:/update_brand')->only(['update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request('paginate')){
            $brands = Brand::name(request('name'))
            ->state(request('state'))
            ->paginate(8);
        }else{
            $brands = Brand::name(request('name'))
            ->state(request('state'))
            ->get();
        }

        return response()->json(['response' => $brands], 200);
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
            'name' => 'bail|required|min:1|max:100|unique:brands,name',
            'description' => 'bail|required|min:1',
            'logo' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            'state' => 'bail|required|integer',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $public_id = str_replace(' ', '-', request('name'));

        # Here we upload an short img
        $logo = \Cloudinary\Uploader::upload(request('logo'),
        array(
            "folder" => "MOS/Logos/Marcas",
            "public_id" => $public_id
        ));

        $brand = Brand::create([
            'name' => request('name'),
            'description' => request('description'),
            'logo' => $logo['secure_url'],
            'public_id' => $public_id,
            'state' => request('state'),
        ]);

        if(!$brand){
            $api = new \Cloudinary\Api();
            $api->delete_resources(array($logo['public_id']));
            return response()->json(['response' => ['error' => ['Error al crear la marca']]], 400);
        }

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
        $brand = Brand::find($id);

        return response()->json(['response' => $brand], 200);
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
            'name' => 'bail|required|min:1|max:100',
            'description' => 'bail|required|min:1',
            'change_logo' => 'required|boolean',
            'state' => 'bail|required|integer',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $brand = Brand::find($id);

        if(!$brand){
            return response()->json(['response' => ['error' => ['Marca no encontrada']]], 400);
        }

        $brand->name = request('name');
        $brand->description = request('description');
        $brand->state = request('state');

        if(request('change_logo')){
            $validator=\Validator::make($request->all(),[
                'logo' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            ]);
            if($validator->fails())
            {
              return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
            }
            # Here we upload the new image
            $upload = \Cloudinary\Uploader::upload(request('logo'),
            array(
                "folder" => "MOS/Logos/Marcas/",
                # Here we must put the same public_id that the previous resource has
                "public_id" => $brand->brand,
                "invalidate"=> true
            ));

            $brand->logo = $upload['secure_url'];

        }

        $brand->update();

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
}
