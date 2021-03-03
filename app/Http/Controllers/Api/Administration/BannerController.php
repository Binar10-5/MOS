<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Language;
use App\Models\MBanner;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_banner')->only(['show', 'index']);
        $this->middleware('permission:/create_banner')->only(['store']);
        $this->middleware('permission:/update_banner')->only(['update', 'destroy']);

        // Get the languaje id
        $language = Language::select('languages.id')
        ->join('countries as c', 'languages.id', 'c.language_id')
        ->where('c.id' ,$request->header('language-key'))
        ->first();
        if($language){
            $this->language = $language->id;
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
    public function index()
    {
        if(request('paginate')){
            $banners = Banner::select('banner.id as son_id', 'banner.name', 'banner.description', 'banner.img_short', 'banner.img_median', 'banner.img_big', 'banner.link', 'mb.state_id as entity_state_id', 'banner.public_id',
            'banner.order_by', 'banner.language_id', 'banner.principal_id', 'banner.state_id')
            ->join('m_banners as mb', 'banner.principal_id', 'mb.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('order_by', 'ASC')
            ->where('banner.language_id', $this->language)
            ->paginate(8);
        }else{
            $banners = Banner::select('banner.id as son_id', 'banner.name', 'banner.description', 'banner.img_short', 'banner.img_median', 'banner.img_big', 'banner.link', 'mb.state_id as entity_state_id', 'banner.public_id',
             'banner.order_by', 'banner.language_id', 'banner.principal_id', 'banner.state_id')
            ->join('m_banners as mb', 'banner.principal_id', 'mb.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('order_by', 'ASC')
            ->where('banner.language_id', $this->language)
            ->get();
        }

        return response()->json(['response' => $banners], 200);

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
            'name' => 'bail|required|min:1|max:75',
            'description' => 'bail|required|min:1',
            'img_short' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            'img_median' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            'img_big' => 'bail|required|image|mimes:jpeg,png,jpg|max:10240',
            'link' => 'bail|required',
            'state_id' => 'bail|required|integer|exists:banners_states,id',
            'entity_state_id' => 'bail|required|integer',
            'principal_id' => 'bail|integer|exists:m_banners,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $public_id = str_replace(' ', '-', request('name'));

        # Here we upload an short img
        $img_short = \Cloudinary\Uploader::upload(request('img_short'),
        array(
            "folder" => "MOS/banners/",
            "public_id" => $public_id."-short"
        ));
        # Here we upload an short img
        $img_median = \Cloudinary\Uploader::upload(request('img_median'),
        array(
            "folder" => "MOS/banners/",
            "public_id" => $public_id."-median"
        ));
        # Here we upload an short img
        $img_big = \Cloudinary\Uploader::upload(request('img_big'),
        array(
            "folder" => "MOS/banners/",
            "public_id" => $public_id."-big"
        ));
        # Get the last banner
        $oder_by = Banner::where('language_id', $this->language)->max('order_by');

        $main_banner = MBanner::find(request('principal_id'));

        if($main_banner){
            $m_banner_id = $main_banner->id;
        }else{
            $m_banner = MBanner::create([
                'name' => request('name'),
                'state_id' => request('state_id')
            ]);

            $m_banner_id = $m_banner->id;
        }

        #Create the banner
        $banner = Banner::create([
            'name' => request('name'),
            'description' => request('description'),
            'img_short' => $img_short['secure_url'],
            'img_median' => $img_median['secure_url'],
            'img_big' => $img_big['secure_url'],
            'link' => request('link'),
            'order_by' => $oder_by+1,
            'public_id' => $public_id,
            'principal_id' => $m_banner_id,
            'state_id' => request('state_id'),
            'language_id' => $this->language,
        ]);

        # If there is a problem delete the cloud photos
        if(!$banner){
            $api = new \Cloudinary\Api();
            $api->delete_resources(array($img_short['public_id']));
            $api->delete_resources(array($img_median['public_id']));
            $api->delete_resources(array($img_big['public_id']));
            return response()->json(['response' => ['error' => ['Error al crear el banner']]], 400);
        }

        return response()->json(['response' => 'Banner creado.'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(request('paginate')){
            $banners = Banner::select('mb.id', 'banner.id as son_id', 'banner.name', 'banner.description', 'banner.img_short', 'banner.img_median', 'banner.img_big', 'banner.link', 'mb.state_id as entity_state_id', 'banner.public_id',
            'banner.order_by', 'banner.language_id', 'banner.principal_id', 'banner.state_id')
            ->join('m_banners as mb', 'banner.principal_id', 'mb.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('order_by', 'ASC')
            ->where('banner.language_id', $this->language)
            ->where('mb.id', $id)
            ->first();
        }else{
            $banners = Banner::select('mb.id', 'banner.id as son_id', 'banner.name', 'banner.description', 'banner.img_short', 'banner.img_median', 'banner.img_big', 'banner.link', 'mb.state_id as entity_state_id', 'banner.public_id',
             'banner.order_by', 'banner.language_id', 'banner.principal_id', 'banner.state_id')
            ->join('m_banners as mb', 'banner.principal_id', 'mb.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('order_by', 'ASC')
            ->where('banner.language_id', $this->language)
            ->where('mb.id', $id)
            ->first();
        }

        return response()->json(['response' => $banners], 200);
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
            'name' => 'required|min:1|max:75',
            'description' => 'required|min:1',
            'link' => 'required',
            'change_short' => 'required|boolean',
            'change_median' => 'required|boolean',
            'change_big' => 'required|boolean',
            'order' => 'required|integer',
            'entity_state_id' => 'bail|required|integer',
            'state_id' => 'required|integer'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $banner = Banner::where('principal_id', $id)->where('language_id', $this->language)->first();

        if(!$banner){
            return response()->json(['response' => ['error' => ['Banner no encontrado.']]], 404);
        }

        $m_banner = MBanner::find($id);

        $m_banner->state_id = request('entity_state_id');
        $m_banner->update();

        $last_order_by = Banner::where('language_id', $this->language)->where('order_by', request('order'))->where('principal_id', '!=', $id)->first();
        if($last_order_by){
            $last_order_by->order_by = $banner->order_by;
            $last_order_by->update();
        }
        # Update user information
        $banner->name = request('name');
        $banner->description = request('description');
        $banner->link = request('link');
        $banner->order_by = request('order');
        $banner->state_id = request('state_id');
        if($banner){
            if(request('change_short')){
                $validator=\Validator::make($request->all(),[
                    'img_short' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                  return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_short'),
                array(
                    "folder" => "MOS/banners/",
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $banner->public_id.'-short',
                    "invalidate"=> true
                ));
                $banner->img_short = $upload['secure_url'];

            }
            if(request('change_median')){
                $validator=\Validator::make($request->all(),[
                    'img_median' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                  return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_median'),
                array(
                    "folder" => "MOS/banners/",
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $banner->public_id.'-median',
                    "invalidate"=> true
                ));
                $banner->img_median = $upload['secure_url'];

            }
            if(request('change_big')){
                $validator=\Validator::make($request->all(),[
                    'img_big' => 'image|max:10240|mimes:jpg,jpeg,png',
                ]);
                if($validator->fails())
                {
                  return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
                }
                # Here we upload the new image
                $upload = \Cloudinary\Uploader::upload(request('img_big'),
                array(
                    "folder" => "MOS/banners/",
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $banner->public_id.'-big',
                    "invalidate"=> true
                ));

                $banner->img_big = $upload['secure_url'];

            }
            $banner->update();

            return response()->json(['response' => 'Banner actualizado.'], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $m_banner = MBanner::find($id);


        if(!$m_banner){
            return response()->json(['response' => ['error' => ['Banner no encontrado']]], 404);
        }
        DB::beginTransaction();
        try{
            $banners = Banner::where('principal_id', $id)->get();

           foreach ($banners as $banner) {
                # Delete the img of Clouddinary
                $api = new \Cloudinary\Api();
                $api->delete_resources(array('MOS/banners/'.$banner->public_id.'-short'));
                $api->delete_resources(array('MOS/banners/'.$banner->public_id.'-median'));
                $api->delete_resources(array('MOS/banners/'.$banner->public_id.'-big'));
                $banners_order = Banner::where('order_by', '>', $banner->order_by)->where('language_id', $banner->language_id)->get();
                foreach ($banners_order as $banner_order) {
                    $banner_order->order_by = $banner_order->order_by - 1;
                    $banner_order->update();
                }
                $banner->delete();
           }
           $m_banner->delete();
        }catch(Exception $e){
            DB::rollback();
        }

        DB::commit();
        return response()->json(['response' => 'Banner eliminado.'], 200);
    }
}
