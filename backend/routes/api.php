<?php

use App\Models\Chatmessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatroomController;
use App\Http\Controllers\ChatmessageController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth
Route::post('/login', [AuthController::class,'login']);
Route::post('/register', [AuthController::class,'register']);

// Retrieving datas
Route::get('/userData', [UserController::class,'getUserData'])->middleware('auth:sanctum');

// User
Route::post('/user/image', [UserController::class,'createUserProfile'])->middleware('auth:sanctum');
Route::get('/setUserOnline', [UserController::class,'setUserOnline'])->middleware('auth:sanctum');
Route::get('/setUserOffline', [UserController::class,'setUserOffline'])->middleware('auth:sanctum');

// Chatroom
Route::post('/chatroom/create', [ChatroomController::class,'createChatroom'])->middleware('auth:sanctum');
Route::get('/loadChatrooms', [ChatroomController::class,'loadChatrooms'])->middleware('auth:sanctum');

// Messages
Route::get('/loadMessages', [ChatmessageController::class,'loadMessages'])->middleware('auth:sanctum');
Route::post('/sendMessage', [ChatmessageController::class,'sendMessage'])->middleware('auth:sanctum');
Route::post('/sendImage', [ChatmessageController::class,'sendImage'])->middleware('auth:sanctum');

// Seen
Route::post('/updateSeen', [ChatmessageController::class,'updateSeen'])->middleware('auth:sanctum');

// Real time typing
Route::post('/sendEventFriendIsTyping', [ChatmessageController::class,'isTyping'])->middleware('auth:sanctum');
Route::post('/sendEventFriendStopTyping', [ChatmessageController::class,'stopTyping'])->middleware('auth:sanctum');


