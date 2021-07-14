<?php

use App\Http\Controllers\Api\Auth\{AccessTokensController, ForgotPasswordController, ResetPasswordController};
use App\Http\Controllers\Api\User\{SocialAuthController, UserController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['as' => 'api.'], function () {

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    })->name('profile');

    //AUTH && FCM GROUP
    Route::post('register', [UserController::class, 'register'])->name('register');
    Route::post('login', [AccessTokensController::class, 'store'])->name('login');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.post');
    Route::post('logout', [AccessTokensController::class, 'destroy'])->name('logout');
    Route::post('auth/social/{provider}', [SocialAuthController::class, 'socialLogin']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::put('profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
        Route::put('password/update', [UserController::class, 'updatePassword'])->name('password.change');
    });
});
