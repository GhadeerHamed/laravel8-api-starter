<?php

namespace App\Http\Controllers\Api\User;

use App\Events\AuthEvent;
use App\Http\Controllers\ApiController;
use App\Http\Requests\API\SocialLoginRequest;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends ApiController
{

    public function socialLogin(SocialLoginRequest $request, $provider): JsonResponse
    {

        if (!$this->checkProviderAvailability($provider)) {
            return $this->respondError('no provider like this.');
        }

        try {
            $provider_user = Socialite::driver($provider)->userFromToken($request->get('access_token'));
        } catch (\Exception $error) {
            return $this->respondError($error->getMessage());
        }

        $user = $this->getExistingUser($provider_user->getId(), $provider);

        if (!$user) {
            $user = $this->userRepository->getUserByEmail($provider_user->email);
            if (!$user) {
                $user = $this->makeNewUser($provider_user);
            }

            $user->social_accounts()->create([
                'provider' => $provider,
                'provider_user_id' => $provider_user->getId()
            ]);
        }

        event(new AuthEvent($user, AuthEvent::ACTION_SOCIAL_LOGIN, ["Provider" => $provider]));

        $token = $user->createToken('API');
        return $this->respondSuccess([
            'access_token' => $token->accessToken,
            'expires_in' => $token->token->expires_at,
            'refresh_token' => null,
            'user' => $user,
        ]);
    }

    private function checkProviderAvailability($provider): bool
    {
        return in_array($provider, ['facebook', 'google']);
    }

    /**
     * @param $provider_id
     * @param $provider
     * @return User|null
     */
    private function getExistingUser($provider_id, $provider): ?User
    {
        $social_account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $provider_id)
            ->first();

        return $social_account->user ?? null;
    }

    private function makeNewUser($social_provider): User
    {
        return User::create([
            'name' => $social_provider->getName(),
            'avatar' => $social_provider->getAvatar(),
            'email' => $social_provider->getEmail()
        ]);
    }
}
