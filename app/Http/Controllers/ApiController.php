<?php

namespace App\Http\Controllers;

use App\Http\Traits\Responsable;
use App\Models\User;
use App\Proxy\HttpKernelProxy;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Laravel\Passport\Client;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Responsable;

    /**
     * Passport Client
     *
     * @var
     */
    protected $passport_client;

    /**
     * A tool for proxying requests to the existing application.
     *
     * @var HttpKernelProxy
     */
    protected HttpKernelProxy $proxy;


    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;
    /**
     * LoginController constructor.
     *
     */
    public function __construct(HttpKernelProxy $proxy,UserRepository $userRepository)
    {
        $this->passport_client = Client::find(config('auth.proxy.client_id'));
        $this->proxy = $proxy;
        $this->userRepository = $userRepository;
    }

    public function createApiPaginator($data): array
    {
        return [
            'total_count' => $data->total(),
            'limit' => $data->perPage(),
            'total_page' => ceil($data->total() / $data->perPage()),
            'current_page' => $data->currentPage(),
        ];
    }

    public function getUser() : User
    {
        return User::query()->find(auth('api')->id());
    }


    /**
     * Create a new access token from a password grant client.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function requestPasswordGrant(Request $request, $user): JsonResponse
    {
        $response = $this->proxy->postJson('oauth/token', [
            'client_id' => $this->passport_client->id,
            'client_secret' => $this->passport_client->secret,
            'grant_type' => "password",
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


}
