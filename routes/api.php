<?php

use App\Http\Controllers\users\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\Posts\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// authentication process
Route::group(['middleware'=>'SanitizeCredentials'],function (){
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/register',[AuthController::class,'register']);
});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

// post routes
Route::group(['middleware'=>['LoggedIn','SanitizeCredentials']],function (){
    Route::apiResource('posts',PostController::class);
    Route::apiResource('user',UserController::class);
    Route::get('user/{user}/posts',[UserController::class,'index']);
    Route::put('user/{id}/post/{post_id}',[UserController::class,'updatePost']);
});

// profile resources


