<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\BannerByCategory;
use App\Models\Language;
use App\Models\Master\MBannerByCategory;
use Illuminate\Http\Request;

class BannersByCategoryController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_banners_by_category')->only(['show', 'index']);
        $this->middleware('permission:/update_banners_by_category')->only(['update', 'destroy']);

        // Get the languaje id
        $language = Language::find($request->header('language-key'));
        if($language){
            $this->language = $request->header('language-key');
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
            $banners = BannerByCategory::select('banners_by_category.id as son_id', 'banners_by_category.name', 'banners_by_category.description', 'banners_by_category.img_short', 'banners_by_category.img_median', 'banners_by_category.img_big', 'banners_by_category.link', 'mb.state as entity_state_id', 'banners_by_category.public_id',
            'banners_by_category.order_by', 'banners_by_category.language_id', 'banners_by_category.principal_id', 'mb.state')
            ->join('m_banners_by_category as mb', 'banners_by_category.principal_id', 'mb.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('order_by', 'ASC')
            ->where('banners_by_category.language_id', $this->language)
            ->paginate(8);
        }else{
            $banners = BannerByCategory::select('banners_by_category.id as son_id', 'banners_by_category.name', 'banners_by_category.description', 'banners_by_category.img_short', 'banners_by_category.img_median', 'banners_by_category.img_big', 'banners_by_category.link', 'mb.state as entity_state_id', 'banners_by_category.public_id',
             'banners_by_category.order_by', 'banners_by_category.language_id', 'banners_by_category.principal_id', 'mb.state')
            ->join('m_banners_by_category as mb', 'banners_by_category.principal_id', 'mb.id')
            ->name(request('name'))
            ->state(request('state'))
            ->orderBy('order_by', 'ASC')
            ->where('banners_by_category.language_id', $this->language)
            ->get();
        }

        return response()->json(['response' => $banners], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $banner = BannerByCategory::select('banners_by_category.id as son_id', 'banners_by_category.name', 'banners_by_category.description', 'banners_by_category.img_short', 'banners_by_category.img_median', 'banners_by_category.img_big', 'banners_by_category.link', 'mb.state as entity_state_id', 'banners_by_category.public_id',
        'banners_by_category.order_by', 'banners_by_category.language_id', 'banners_by_category.principal_id', 'banners_by_category.state_id')
        ->join('m_banners_by_category as mb', 'banners_by_category.principal_id', 'mb.id')
        ->where('banners_by_category.language_id', $this->language)
        ->where('mb.id', $id)
        ->first();

        return response()->json(['response' => $banner], 200);

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
            'link' => 'bail',
            'change_short' => 'required|boolean',
            'change_median' => 'required|boolean',
            'change_big' => 'required|boolean',
            'state' => 'required|integer'
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $banner = BannerByCategory::where('principal_id', $id)->where('language_id', $this->language)->first();

        if(!$banner){
            return response()->json(['response' => ['error' => ['Banner no encontrado.']]], 404);
        }

        $m_banner = MBannerByCategory::find($id);

        $m_banner->state = request('state');
        $m_banner->update();

        # Update user information
        $banner->name = request('name');
        $banner->description = request('description');
        $banner->link = request('link');
        $banner->state_id = 1;
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
                    "folder" => "MOS/banners-by-category/",
                    # Here we must put the same public_id that the previous resource has
                    "public_id" => $banner->public_id.'-short',
                    "invalidate"=> true
                ));
                $banner->img_short = $upload['secure_url'];

            }
            return response()->json(['response' => ['error' => ['Hola']]],400);

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
                    "folder" => "MOS/banners-by-category/",
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
                    "folder" => "MOS/banners-by-category/",
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

    }
}
