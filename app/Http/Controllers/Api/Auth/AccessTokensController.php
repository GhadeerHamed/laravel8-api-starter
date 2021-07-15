<?php

namespace App\Http\Controllers\Api\Auth;


use App\Events\AuthEvent;
use App\Http\Controllers\ApiController;
use App\Http\Requests\API\LoginUserRequest;
use App\Http\Requests\API\UpdateTokenRequest;
use App\Models\User;
use App\Proxy\HttpKernelProxy;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class AccessTokensController extends ApiController
{

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username(): string
    {
        return 'email';
    }

    /**
     * @SWG\Post(
     *        path="/api/login",
     *        tags={"users"},
     *        operationId="login",
     *        summary="Fetch user access token",
     * 		@SWG\Parameter(
     *            name="body",
     *            in="body",
     *            required=true,
     *            description="Registered username",
     *     @SWG\Schema(
     *              @SWG\Property(property="username", type="string", example="test2"),
     *              @SWG\Property(property="password", type="string", example="12345678"),
     *          ),
     *        ),
     * 		@SWG\Response(
     *            response=200,
     *            description="Password of the account",
     *          x={
     *              "id":"1",
     *               "name":"test"
     *          }
     *        ),
     *    )
     *
     */
    public function store(LoginUserRequest $request): JsonResponse
    {
        $user = $this->userRepository->getUserByEmail($request->email);

        try {
            return $this->requestPasswordGrant($request, $user);
        } catch (JsonException $e) {
            return $this->respondError($e->getMessage());
        }
    }


    /**
     * Refresh an access token.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(UpdateTokenRequest $request): JsonResponse
    {
        $response = $this->proxy->postJson('oauth/token', [
            'client_id' => $this->passport_client->id,
            'client_secret' => $this->passport_client->secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'scopes' => '[*]',
        ]);

        if ($response->isSuccessful()) {
            return $this->sendSuccessResponse($response, null);
        }

        $res = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        return $this->respondError($res["message"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        $user = $this->userRepository->getById(Auth::id());
        $user->tokens()->delete();
        event((new AuthEvent($user, AuthEvent::ACTION_LOGOUT, [])));
        return $this->respondMessage("Logged out");
    }
}
