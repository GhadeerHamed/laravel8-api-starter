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
    Route::group(['as' => 'auth.'], function () {
        Route::post('register', [UserController::class, 'register'])->name('register');
        Route::post('login', [AccessTokensController::class, 'store'])->name('login');
        Route::post('update_token', [AccessTokensController::class, 'update'])->name('update_token');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.post');
        Route::post('logout', [AccessTokensController::class, 'destroy'])->name('logout')->middleware(["auth:api"]);
        Route::post('auth/social/{provider}', [SocialAuthController::class, 'socialLogin'])->name('social_login');
    });


    Route::group(['middleware' => 'auth:api', 'as' => 'user.'], function () {
        Route::put('profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
        Route::put('password/update', [UserController::class, 'updatePassword'])->name('password.change');

        Route::get('addresses', [UserController::class, 'getAddresses'])->name('addresses.list');
        Route::post('addresses', [UserController::class, 'storeAddress'])->name('addresses.store');
        Route::put('addresses/{id}', [UserController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('addresses/{id}', [UserController::class, 'deleteAddress'])->name('addresses.delete');
    });
});
