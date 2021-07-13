<?php

namespace App\Http\Controllers\Api\Auth;


use App\Http\Controllers\ApiController;
use App\Http\Requests\API\LoginUserRequest;
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
     * A tool for proxying requests to the existing application.
     *
     * @var HttpKernelProxy
     */
    protected HttpKernelProxy $proxy;
    protected UserRepository $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(HttpKernelProxy $proxy, UserRepository $userRepository)
    {
        $this->middleware('auth:api')->except(['store', 'update']);
        $this->proxy = $proxy;
        $this->userRepository = $userRepository;
    }

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
            $this->respondError($e->getMessage());
        }
    }

    /**
     * Create a new access token from a password grant client.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function requestPasswordGrant(Request $request, $user): JsonResponse
    {
        $response = $this->proxy->postJson('oauth/token', [
            'client_id' => config('auth.proxy.client_id'),
            'client_secret' => config('auth.proxy.client_secret'),
            'grant_type' => config('auth.proxy.grant_type'),
            'username' => $user->email,
            'password' => $request->password,
            'scopes' => '[*]'
        ]);
        if ($response->isSuccessful()) {
            return $this->sendSuccessResponse($response, $user);
        }

        $res = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        return $this->respondError($res["message"]);
    }

    /**
     * Return a successful response for requesting an api token.
     *
     * @param Response $response
     * @return JsonResponse
     * @throws JsonException
     */
    public function sendSuccessResponse(Response $response, $user): JsonResponse
    {
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $content = [
            'access_token' => $data['access_token'],
            'expires_in' => Carbon::now()->addSeconds($data['expires_in'])->format("Y-m-d H:i:s"),
            'refresh_token' => $data['refresh_token'],
            'user' => $user,
        ];
        return $this->respondSuccess($content, $response->getStatusCode())->cookie(
            'refresh_token',
            $data['refresh_token'],
            10 * 24 * 60,
            "",
            "",
            true,
            true
        );
    }

    /**
     * Refresh an access token.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request): JsonResponse
    {
        $token = $request->cookie('refresh_token');
        if (!$token) {
            throw ValidationException::withMessages([
                'refresh_token' => trans('oauth.missing_refresh_token')
            ]);
        }
        $response = $this->proxy->postJson('oauth/token', [
            'client_id' => config('auth.proxy.client_id'),
            'client_secret' => config('auth.proxy.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
            'scopes' => '[*]',
        ]);
        if ($response->isSuccessful()) {
            return $this->sendSuccessResponse($response, null);
        }
        return $this->respondError($response->getContent());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        Auth::user()->token()->revoke();
        return $this->respondMessage("Logged out");
    }
}
