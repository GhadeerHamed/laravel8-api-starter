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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends ApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(UserRequest $request): JsonResponse
    {
        $user = $this->userRepository->add($request);
        try {
            $user->generateActivationCode()->save();
        } catch (\Exception $e) {
        }

        if (!$user->token) {
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

            if (!$user->token) {
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

    public function updatePassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!Hash::check($request->get('old_password'), $user->password)) {
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

    public function profile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user)
            return $this->respondError(__('api.user_not_found'));

        return $this->respondSuccess($user);
    }

    public function profileUpdate(UpdateUserRequest $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->respondError(__('api.user_not_found'));
        }

        $this->userRepository->update($request, $user);

        return $this->respondSuccess([
            'user' => $user
        ]);
    }

}
