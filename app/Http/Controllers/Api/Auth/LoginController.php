<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';


    /**
 * @SWG\Post(
 *     path="/create",
 *     tags={"ss"},
 *     description="Return a user's first and last name",
 *     @SWG\Parameter(
 *         name="firstname",
 *         in="query",
 *         type="string",
 *         description="Your first name",
 *         required=true,
 *     ),
 *     @SWG\Parameter(
 *         name="lastname",
 *         in="query",
 *         type="string",
 *         description="Your last name",
 *         required=true,
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="OK",
 *     ),
 *     @SWG\Response(
 *         response=422,
 *         description="Missing Data"
 *     )
 * )
 */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
