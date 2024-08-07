<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try{
            $posts = Post::all();
            return response([
                'status'=>true,
                'posts'=>$posts
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = validator::make($request->all(),[
                'title'=>'string|required|max:40',
                'image'=>'required|image',
                'postContent'=>'required|string',
                'tagComment'=>'string|required',
                'user_id'=>'numeric'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>$validator->errors()
                ],401);
            }
            $path = Storage::disk('public')->putFile('postsImage',$request->file('image'));

            // connect with machine
            $data = '{"text":"'.$request->tagComment.'"}';
            $responseTag = Http::withHeaders(['Content_type'=>'application/json'])->withBody($data,'application/json')->post('http://127.0.0.1:5000/predict');
            if(json_decode($responseTag['prediction'])==1){
                $responseTag = 'negative';
            }else{
                $responseTag='positive';
            }

            $post = Post::create([
                'title'=>$request->title,
                'content'=>$request->postContent,
                'image'=>$path,
                'user_id'=>$request->user_id,
                'tag'=>$responseTag

            ]);

            if($post){
                Log::info('User '.$request->user_id.'has created a post with title '. $request->title);
                return response()->json([
                    'status'=>true,
                    'message'=>'Post has been published successfully'
                ],200);
            }
            return response()->json([
                'status'=>false,
                'message'=>'Post hasn\'t been published'
            ],422);

        }catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try{
            $post = Post::where('id',$id)->first();
            if($post){
                return response()->json([
                    'status'=>true,
                    'post'=>$post
                ],200);
            }
            return response()->json([
                'status'=>false,
                'message'=>'wrong id of the post'
            ],422);
        }catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
//    public function edit(string $id)
//    {
//        //
//    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $post = Post::where('id',$id)->delete();
            if($post){
                Log::info('Post '.$id.'has been deleted');
                return response()->json([
                    'status'=>true,
                    'message'=>'Post has been deleted successfully'
                ],200);
            }
            return response()->json([
                'status'=>false,
                'message'=>'Post hasn\'t been deleted'
            ],422);

        }catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }

    }
}
