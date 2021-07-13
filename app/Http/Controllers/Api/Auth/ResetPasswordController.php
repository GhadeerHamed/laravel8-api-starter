<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected string $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function sendResetResponse(Request $request, $response): JsonResponse
    {
        $user = (new User)->whereEmail($request->get('email'))->first();
        return $this->respondSuccess(['user' => $user]);
    }

    protected function sendResetFailedResponse(Request $request, $response): JsonResponse
    {
        return $this->respondError(trans($response));
    }

    protected function resetPassword($user, $password): JsonResponse
    {
        $user->password = $password;

        $user->setRememberToken(Str::random(60));

        $user->save();

        return $this->respondSuccess(['user' => $user]);
    }

}
