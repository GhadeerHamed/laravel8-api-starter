<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class FbController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookSignin()
    {

        dd(request('code'));
        $user = Socialite::driver('facebook')->user();
        $facebookId = User::where('facebook_id', $user->id)->first();

            if ($facebookId) {
                Auth::login($facebookId);
                return redirect('/dashboard');
            } else {
                $createUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'facebook_id' => $user->id,
                    'password' => encrypt('john123')
                ]);

                Auth::login($createUser);
                return redirect('/dashboard');
            }

    }
}
