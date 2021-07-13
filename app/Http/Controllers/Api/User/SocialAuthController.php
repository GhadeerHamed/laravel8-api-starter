<?php

namespace App\Http\Controllers\Api\User;

use App\Classes\FacebookClient;
use App\Classes\GoogleClient;
use App\Http\Controllers\ApiController;
use App\Http\Requests\API\SocialLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends ApiController {


    public function socialLogin($provider, SocialLoginRequest $request): JsonResponse
    {

        if(!$this->checkProviderAvailability($provider)) {
            return $this->respondError('no provider like this.');
        }

        try {
            $client = Socialite::driver($provider)->userFromToken($request->get('access_token'));
        } catch(\Exception $error) {
            return $this->respondError($error->getMessage());
        }

        $existingClient = $this->getExistingClient($client->email, $provider);

        if ($existingClient){
            $token = $existingClient->createToken('API');

            return $this->respondSuccess([
                'client' => $existingClient,
                'token' => $token->plainTextToken
            ]);
        }

        $client = $this->makeNewSocialClient($client, $provider);
        $token = $client->createToken('API');

        return $this->respondSuccess([
            'client' => $client,
            'token' => $token->plainTextToken
        ]);
    }

    private function checkProviderAvailability($provider): bool
    {
        return in_array($provider, ['facebook', 'google']);
    }

    private function getExistingClient($email, $provider)
    {
        return User::query()->where([
            ['email', $email],
            ['provider', $provider]
        ])->first();
    }

    private function makeNewSocialClient($client, $provider) : User
    {
        switch ($provider)
        {
            case User::FACEBOOK_PROVIDER:
                $client = new FacebookClient($client);
                break;
            case User::GOOGLE_PROVIDER:
                $client = new GoogleClient($client);
                break;
        }
        $newClient = $client->makeClient();
        $newClient->save();

        return $newClient;
    }
}
