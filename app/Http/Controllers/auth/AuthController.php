<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;


class AuthController extends Controller
{
    //
    use HasApiTokens;
    public function login (Request $request){
        try {

            // validation
            $validator = validator::make($request->all(),[
                'email'=>'email|string|required',
                'password'=>'string|required|min:8'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>$validator->errors(),
                ],422 );
            }

            $user = Auth::attempt($request->only(['email','password']));
            if(!$user){
                return response()->json([
                    'status'=>false,
                    'message'=>'Email or Password are not correct'
                ],401);
            }

            $user = User::where('email',$request->email)->first();
            $token = $user->createToken('api_token')->plainTextToken;
            Log::info($user->name.'login successfully');
            return response()->json([
                'status'=>true,
                'message'=>'user logged in successfully',
                'data'=> [
                    'user'=>$user,
                    'token'=>$token
                ]
            ],200);

        }
        catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    public function register(Request $request){

        try{
            //validation
            $validator = validator::make($request->all(),[
                'email'=>'email|string|required',
                'name'=>'string|required',
                'bio'=>'string|required|max:30',
                'password'=>'string|required|min:8',
                'image'=>'required'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>$validator->errors()
                ],422);
            }
            $extention = $request->file('image')->getClientOriginalExtension();
            if(!in_array($extention,['png','PNG','JEPG','jepg','jpg','JPG'])){
                return response()->json([
                    'status'=>false,
                    'message'=>'This is not a photo [photo must be [\'png\',\'PNG\',\'JEPG\',\'jepg\',\'jpg\',\'JPG\']]'
                ],422);
            }
            $path = Storage::disk('public')->putFile('avatars',$request->file('image'));
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'image'=>$path,
                'bio'=>$request->bio
            ]);

            $AttemptedUser = Auth::attempt($request->only(['email','password']));
            if(!$AttemptedUser){
                return response()->json([
                    'status'=>false,
                    'message'=>'Can\'t return a user data'
                ],401);
            }

            $token = $user->createToken('api_token')->plainTextToken;
            Log::info($user->name.'Signup successfully');
            return response()->json([
                'status'=>true,
                'message'=>'user logged in successfully',
                'data'=> [
                    'user'=>$user,
                    'token'=>$token
                ]
            ],200);
        }catch (\Exception $e){
            return response()->json([
                    'status'=>false,
                    'message'=>'server error',
                    'error'=>$e->getMessage(),
                ],500);
        }
}
    public function logout(Request $request){
        try{
            if($request->user()->currentAccessToken()->delete()){
                return response()->json([
                    'status'=>true,
                    'message'=>'Successfully logout'
                ],200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Can\'t logout'
                ], 401);
            }

        }catch (\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'server error',
                'error'=>$e->getMessage(),
            ],500);
        }
    }
}
