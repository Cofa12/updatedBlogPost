<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Exception;
//use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PDOException;
use Illuminate\Database\QueryException;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $id)
    {
        //
        try{
            $posts = Post::where('user_id',$id)->get();
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
//    public function store(Request $request)
//    {
//        //
//    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try{
            $posts = Post::where('user_id',$id)->get();
            if($posts){
                return response()->json([
                    'status'=>true,
                    'posts'=>$posts
                ],200);
            }
            return response()->json([
                'status'=>false,
                'message'=>'wrong id of the post'
            ],422);
        }catch(\Exception $e){
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $validator = validator::make($request->all(),[
                'name'=>'string|max:40',
                'image'=>'image|Nullable',
                'email'=>'email|string'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>$validator->errors()
                ],401);
            }
            $path=null;
            if($request->file('image')) {
                $path = Storage::disk('public')->putFile('postsImage', $request->file('image'));
            }
            $user = User::where('id',$id)->update([
                'name'=>$request->name,
                'email'=>$request->email
            ]);
            if($path){
                $user->image=$path;
                $user->save();
            }

            if($user){
                $user = User::where('id',$id)->first();
                return response()->json([
                    'status'=>true,
                    'message'=>'Post has been edited successfully',
                    'user'=>$user
                ],200);
            }
            return response()->json([
                'status'=>false,
                'message'=>'Post hasn\'t been edited'
            ],422);

        }catch (QueryException $e){
            return response()->json([
                'status'=>false,
                'message'=>'this email has been chosen before',
            ],401);
        }

        catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try{
            $post = User::where('id',$id)->delete();
            if($post){
                return response()->json([
                    'status'=>true,
                    'message'=>'User has been deleted successfully'
                ],200);
            }
            return response()->json([
                'status'=>false,
                'message'=>'User hasn\'t been deleted'
            ],422);

        }catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }

    }

    public function updatePost(Request $request, string $id,string $post_id)
    {
        try {
            $validator = validator::make($request->all(), [
                'title' => 'string|max:40',
                'image' => 'image|Nullable',
                'postContent' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 401);
            }
            $path = null;
            if($request->file('image')){
                $path = Storage::disk('public')->putFile('postsImage', $request->file('image'));
            }
            $post = Post::where('id', $post_id)->where('user_id', $id)->update([
                'title' => $request->title,
                'content' => $request->postContent
            ]);
            if($path){
                $post->image=$path;
                $post->save();
            }

            if ($post) {
                Log::info('User ' . $id . 'has created a post ' . $post_id);
                return response()->json([
                    'status' => true,
                    'message' => 'Post has been edited successfully'
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => 'Post hasn\'t been edited'
            ], 422);

        }catch (\Illuminate\Database\QueryException $r){
            return response()->json([
                'status' => false,
                'message' => 'couldn\'t connect with xamp try to run it !!',
                'error' => $r->getMessage(),
            ], 500);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
