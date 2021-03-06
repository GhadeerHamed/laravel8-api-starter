<?php

namespace App\Http\Controllers\Api\User;

use App\Events\AuthEvent;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Requests\API\UpdateTokenRequest;
use App\Http\Requests\API\ResetPasswordConfirmRequest;
use App\Http\Requests\API\ResetPasswordRequest;
use App\Http\Requests\API\UpdateUserRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\AddressCollection;
use App\Models\Address;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class UserController extends ApiController
{

    public function register(UserRequest $request): JsonResponse
    {
        $user = $this->userRepository->add($request);
        try {
            $user->generateActivationCode()->save();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
        event((new AuthEvent($user, AuthEvent::ACTION_REGISTER, [])));

        try {
            return $this->requestPasswordGrant($request, $user);
        } catch (JsonException $e) {
            return $this->respondError($e->getMessage());
        }
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

    public function getAddresses(): JsonResponse
    {
        $user = Auth::user();
        $addresses = $this->userRepository->getAddresses($user);

        return $this->respondSuccess(new AddressCollection($addresses), $this->createApiPaginator($addresses));
    }


    public function storeAddress(StoreAddressRequest $request): JsonResponse
    {
        $user = Auth::user();
        $address = $this->userRepository->storeAddress($user, $request->all());
        return $this->respondSuccess($address);
    }

    public function updateAddress(UpdateAddressRequest $request, $id): JsonResponse
    {
        $user = Auth::user();
        $address = $this->userRepository->updateAddress($user, $id, $request->all());
        return $this->respondSuccess($address);
    }

    public function deleteAddress($id): JsonResponse
    {
        $user = Auth::user();
        $address = $this->userRepository->deleteAddress($user, $id);
        return $this->respondSuccess($address);
    }

}
