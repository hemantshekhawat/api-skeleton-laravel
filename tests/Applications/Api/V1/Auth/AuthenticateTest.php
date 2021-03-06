<?php

namespace App\Tests\Applications\Api\V1\Auth;

use App\Tests\Applications\Api\V1\V1TestCase;
use App\Tests\Traits\DatabaseSeeds;

class AuthenticateTest extends V1TestCase
{
    use DatabaseSeeds;

    /**
     * The user credentials for a valid user
     *
     * @var array
     */
    private $loginUser;

    public function __construct()
    {
        parent::__construct();

        $this->loginUser = [
            'email' => 'test@test.com',
            'password' => '12345678'
        ];
    }

    public function test_can_login_a_user()
    {
        $response = $this->callAPI('POST', 'login', $this->loginUser);

        $this->assertResponseOk();
        $this->assertIfTokenIsValid(json_decode($response->content())->token);
    }

    public function test_can_login_a_user_with_wrong_credentials()
    {
        $wrong_user = ['email' => 'wrong@user.com', 'password' => 'wrong_pass'];
        $response = $this->callAPI('POST', 'login', $wrong_user);

        $this->seeJsonEquals(['message' => 'Invalid Credentials','status_code' => 401], $response->content());
        $this->assertResponseStatus(401);
        $this->notSeeInDatabase('users', $wrong_user);
    }

    public function test_can_login_a_user_with_invalid_credentials()
    {
        $wrong_user = ['email' => 'wrong.user', 'password' => 'wrong_pass'];
        $response = $this->callAPI('POST', 'login', $wrong_user)->content();

        $this->assertTrue(array_key_exists('errors.email.0', array_dot(json_decode($response, 1))));
        $this->assertResponseStatus(422);
        $this->notSeeInDatabase('users', $wrong_user);
    }
}
