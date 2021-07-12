<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\ApiController;
use App\Http\Requests\API\LoginUserRequest;
use App\Http\Requests\API\ResetPasswordConfirmRequest;
use App\Http\Requests\API\ResetPasswordRequest;
use App\Http\Requests\API\UpdateUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends ApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    public function register(UserRequest $request): JsonResponse
    {
        $user = $this->userRepository->add($request);
        try {
            $user->generateActivationCode()->save();
        } catch (\Exception $e) {
        }

        if(!$user->token){
            $token = $user->createToken('API');
            $user->token = $token->plainTextToken;
            $user->save();
        }

        return $this->respondSuccess(
            [
                'token' => $user->token,
                'user' => $user
            ]
        );
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        if (auth('api')->attempt($credentials)) {
            $user = User::whereId(auth('api')->id())->first();

            if (!$user->token){
                $token = $user->createToken('API');
                $user->token = $token->plainTextToken;
                $user->save();
            }
            return $this->respondSuccess([
                'token' => $user->token,
                'user' => $user
            ]);
        }
        return $this->respondError(__('api.username_or_password_invalid'));
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::whereEmail($request->get('email'))->first();
        if (!$user) {
            return $this->respondError(__('api.user_not_found'));
        }
        if(!Hash::check($request->get('old_password'), $user->password)) {
            return $this->respondError(__('api.wrong_password'));
        }

        $user->password = ($request->get('new_password'));
        $user->save();
        return $this->respondSuccess($user);
    }

    public function resetPasswordConfirm(ResetPasswordConfirmRequest $request): JsonResponse
    {
        $user = User::whereEmail($request->get('email'))->first();

        if ($user->code !== $request->get('code')) {
            return $this->respondError(__('api.error_code'));
        }

        $user->password = $request->get('password');
        $user->save();

        return $this->respondSuccess($user);
    }

    public function forgetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email'
        ]);

        if ($validator->fails()) {
            Log::error($validator->errors());
            return $this->respondError($validator->errors()->first(), $validator->errors()->getMessages());
        }

        try {
            $validatedData = $validator->validated();
            $user = User::whereEmail($validatedData["email"])->first();
            $user->generatePasswordToken()->save();

        } catch (ValidationException $e) {
            Log::error($e->getMessage());
        }

        return $this->respondSuccess();

    }

    public function checkPasswordAndChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'reset_code'  => 'required',
            'password' => 'required|confirmed'
        ]);

        if ($validator->fails()) {
            Log::error($validator->errors());
            return view('auth.reset-password-result', ['message' => $validator->errors()->first()]);
        }

        try {
            $validatedData = $validator->validated();
            $user = User::whereEmail($validatedData["email"])->first();
            $checkCode = $user->checkPasswordCode($validatedData["reset_code"]);
            if($checkCode){
                $user->reset_verified = "yes";
                $user->reset_token = null;
                $passwordChanged = $user->changePassword($validatedData["password"]);
                if($passwordChanged)
                    return view('auth.reset-password-result', ['message' => __('api.password_changed')]);

                return view('auth.reset-password-result', ['message' => __('api.password_not_changed')]);
            }

        } catch (ValidationException $e) {
            Log::error($e->getMessage());
        }

        return back()->with('error', __('api.code_not_valid'));
//        return view('auth.reset-password-result', ['message' => __('api.code_not_valid')]);

    }

    public function profile(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        if(!$user)
            return $this->respondError(__('api.user_not_found'));

        return $this->respondSuccess($user);
    }

    public function profilePost(UpdateUserRequest $request): JsonResponse
    {
        $user = $this->getUser($request);
        if (!$user)
            return $this->respondError(__('api.user_not_found'));


        $this->userRepository->update($request, $user);

        if (!$user->token){
            $token = $user->createToken('API');
            $user->token = $token->plainTextToken;
            $user->save();
        }

        return $this->respondSuccess([
            'token' => $user->token,
            'user' => $user
        ]);
    }

}
