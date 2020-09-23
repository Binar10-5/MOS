<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Master\MVideoHome;
use App\Models\VideoHome;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideosHomeController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('permission:/list_h_videos')->only(['show', 'index']);
        $this->middleware('permission:/create_h_video')->only(['store']);
        $this->middleware('permission:/update_h_video')->only(['update', 'destroy']);

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
            $videos = VideoHome::select('video_home.id as son_id', 'video_home.name', 'video_home.description', 'video_home.video', 'video_home.principal_id', 'video_home.language_id', 'mv.state')
            ->join('m_video_home as mv', 'video_home.principal_id', 'mv.id')
            ->name(request('name'))
            ->state(request('state'))
            ->where('video_home.language_id', $this->language)
            ->paginate(8);
        }else{
            $videos = VideoHome::select('video_home.id as son_id', 'video_home.name', 'video_home.description', 'video_home.video', 'video_home.principal_id', 'video_home.language_id', 'mv.state')
            ->join('m_video_home as mv', 'video_home.principal_id', 'mv.id')
            ->name(request('name'))
            ->state(request('state'))
            ->where('video_home.language_id', $this->language)
            ->get();
        }

        return response()->json(['response' => $videos], 200);
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
            'video' => 'bail|required',
            'state' => 'bail|required|integer|exists:banners_states,id',
            'principal_id' => 'bail|integer|exists:m_banners,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $main_video = MVideoHome::find(request('principal_id'));

        DB::beginTransaction();
        try{

            if($main_video){
                $m_video_id = $main_video->id;
            }else{
                $m_video = MVideoHome::create([
                    'name' => request('name'),
                    'state' => request('state')
                ]);

                $m_video_id = $m_video->id;
            }

            #Create the banner
            $video = VideoHome::create([
                'name' => request('name'),
                'description' => request('description'),
                'video' => request('video'),
                'principal_id' => $m_video_id,
                'state' => request('state'),
                'language_id' => $this->language,
            ]);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['response' => ['error' => [$e->getMessage()]]], 400);
        }

        DB::commit();
        return response()->json(['response' => 'Success.'], 200);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $video = VideoHome::select('video_home.id as son_id', 'video_home.name', 'video_home.description', 'video_home.video', 'video_home.principal_id', 'video_home.language_id', 'mv.state')
        ->join('m_video_home as mv', 'video_home.principal_id', 'mv.id')
        ->where('video_home.language_id', $this->language)
        ->where('mv.id', $id)
        ->first();

        return response()->json(['response' => $video], 200);

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
            'name' => 'bail|required|min:1|max:75',
            'description' => 'bail|required|min:1',
            'video' => 'bail|required',
            'state' => 'bail|required|integer',
            'principal_id' => 'bail|integer|exists:m_video_home,id',
        ]);
        if($validator->fails())
        {
          return response()->json(['response' => ['error' => $validator->errors()->all()]],400);
        }

        $video = VideoHome::where('principal_id', $id)->where('language_id', $this->language)->first();

        if(!$video){
            return response()->json(['response' => ['error' => ['Video no encontrado.']]], 404);
        }

        $m_video = MVideoHome::find($id);

        if(request('state') == 1 && $m_video->state != 1){
            $m_video_update = MVideoHome::where('id', '!=', $id)->update(['state' => 2]);
        }

        DB::beginTransaction();
        try{
            $m_video->state = request('state');
            $m_video->update();
            $video->name = request('name');
            $video->description = request('description');
            $video->video = request('video');
            $video->update();
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['response' => ['error' => [$e->getMessage()]]], 400);
        }
        DB::commit();
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
