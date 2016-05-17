<?php

namespace App\Core\Api\V1\Http\Controllers\Auth;

use App\Domains\Users\Repositories\UserRepositoryInterface;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Core\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Core\Api\V1\Http\Requests\Auth\AuthenticateRequest;
use App\Core\Api\V1\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    use Helpers;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {

        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate a user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(AuthenticateRequest $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->response->error('invalid_credentials', 401);
            }
        } catch (JWTException $e) {
            return $this->response->error('could_not_create_token', 500);
        }

        return $this->response->array(compact('token'));
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $fields = $request->only('name', 'company_name', 'cnpj', 'address', 'city', 'telephone', 'email', 'password');

        $user = $this->userRepository->create($fields);

        // Todo Send registration email, fire register event, etc

        $token = JWTAuth::fromUser($user);

        return $this->response->array(compact('token'));
    }

    /**
     * Return success if is authenticated
     *
     * @return mixed
     */
    public function validateToken()
    {
        return $this->response->array(['status' => 'success'])->statusCode(200);
    }
}
