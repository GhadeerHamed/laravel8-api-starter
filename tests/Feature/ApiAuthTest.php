<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if user can register.
     * @test
     * @return void
     */
    public function user_can_register_api(): void
    {
        $this->createUserForTest();

        $this->assertDatabaseCount(User::class, 1);
        $this->assertEquals(User::query()->first()->id, $this->user->id);
    }

    /**
     * Check access token can be obtained [API Login].
     * @test
     * @return void
     */
    public function user_can_login_with_correct_credentials_api(): void
    {
        $this->createUserForTest();
        $credentials = ['email' => 'user@test.com', 'password' => 'secret'];

        $response = $this->post('api/login', $credentials);

        $this->assertNotEmpty($response['content']);
        $this->assertNotNull($response['content']);
        $response->assertJsonStructure(['content' => ['access_token', 'refresh_token', 'user']]);
        $this->assertEquals($this->user->id, $response['content']['user']['id']);
        $response->assertStatus(200);

        $this->access_token = $response['content']['access_token'];
        $this->setAuthorizationHeader();

        $profile = $this->get('api/user'); //->withHeaders(['Authorization' => 'Bearer ' . $access_token]);

        //Must be authenticated=true while correct access token exists in the headers
        $this->assertAuthenticated('api');

        //generated user must be same retrieved user
        $this->assertEquals($this->user->fresh()->toJson(), $profile->content());
    }
    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function user_cant_login_with_incorrect_credentials_api(): void
    {

        $this->createUserForTest();

        // Case 1: wrong email
        $credentials = ['email' => 'wrong@test.com', 'password' => 'secret'];
        $response = $this->post('api/login', $credentials);
        $response->assertJsonStructure(['error_validation' => ['email']]);
        $this->assertNull($response['content']);

        $this->access_token = $response['content']['access_token'] ?? null;
        $this->setAuthorizationHeader();

        $this->assertEquals(false, $this->isAuthenticated('api'));
        $response->assertStatus(400);

        // Case 2: wrong password
        $credentials = ['email' => 'user@test.com', 'password' => 'wrong_pass'];
        $response = $this->post('api/login', $credentials);

        $response->assertJsonStructure(['error_des', 'error_validation']);
        $this->assertNull($response['content']);

        $this->access_token = $response['content']['access_token'] ?? null;
        $this->setAuthorizationHeader();

        $this->assertEquals(false, $this->isAuthenticated('api'));
        $response->assertStatus(400);

    }

}
