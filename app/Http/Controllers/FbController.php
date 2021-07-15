<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class FbController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookSignin()
    {
        $user = Socialite::driver('facebook')->user();
        echo("<div style='text-align: center;margin-top: 2rem;'><p>" . $user->token . "</p></div>");
        exit();
    }
}
