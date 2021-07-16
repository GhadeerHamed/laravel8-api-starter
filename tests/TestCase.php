<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

    protected $client, $user, $access_token;

    public function setUp(): void
    {
        parent::setUp();

        $this-> client = Client::create([
            'id' => config('auth.proxy.client_id'),
            'name' => 'Laravel-starter_api Password Grant Client',
            'secret' => config('auth.proxy.client_secret'),  //'V8BSykJxMTJOfUh8elJ7bNe6FmYFXMoXHWCQYVgQ',
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0
        ]);


        config(['auth.proxy.client_id' => $this->client->id]);

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $this->client->id,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);

    }


    public function createUserForTest($data = ['name' => 'Name', 'email' => 'user@test.com', 'password' => 'secret'], $additional = []): void
    {
        $this->user = User::create(array_merge($data, $additional));
    }

    protected function setAuthorizationHeader(): void
    {
        $this->withHeader('Authorization', 'Bearer ' . $this->access_token);
    }
}
