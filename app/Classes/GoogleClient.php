<?php

namespace App\Classes;

use App\Interfaces\SocialClient;
use App\Models\User;

class GoogleClient implements SocialClient
{
    private $socialClient;
    private $client;

    public function __construct($client)
    {
        $this->socialClient = $client;
        $this->client = $client->user;
    }

    function getFirstName()
    {
        return $this->client['given_name'];
    }

    function getLastName()
    {
        return $this->client['family_name'];
    }

    function getEmail()
    {
        return $this->client['email'];

    }

    function makeClient()
    {
        return User::query()->make([
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'provider' => User::GOOGLE_PROVIDER,
            'provider_id' => $this->socialClient->id
        ]);
    }
}
