<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\{AuthController, UserController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//AUTH && FCM GROUP
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('change-password', [UserController::class, 'resetPassword']);

Route::post('forget-password', [UserController::class, 'forgetPassword']);
Route::post('reset-password', [UserController::class, 'resetPasswordConfirm']);

Route::post('forget-password-confirm', [UserController::class, 'checkPasswordAndChange']);
Route::post('social-login/{provider}',[AuthController::class, 'socialLogin']);
