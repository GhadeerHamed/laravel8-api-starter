<?php

namespace App\Classes;

use App\Interfaces\SocialClient;
use App\Models\User;

class FacebookClient implements SocialClient
{
    private $socialClient;
    private $client;

    public function __construct($client)
    {
        $this->socialClient = $client;
        $this->client = $client->user;
    }

    function makeClient()
    {
        return User::query()->make([
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'provider' => User::FACEBOOK_PROVIDER,
            'provider_id' => $this->socialClient->id
        ]);
    }

    function getFirstName(): string
    {
        $fullName = explode(' ', $this->client['name']);
        return $fullName[0];
    }

    function getLastName(): string
    {
        $fullName = explode(' ', $this->client['name']);
        if ($fullName[1])
            return $fullName[1];

        return "";
    }

    function getEmail()
    {
        return $this->client['email'];
    }
}
