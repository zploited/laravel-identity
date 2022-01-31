<?php

namespace Zploited\Laravel\Identity\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Zploited\Laravel\Identity\Identity;

class IdentityGuard implements Guard
{
    /**
     * @var UserProvider
     */
    protected UserProvider $provider;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Authenticatable|null
     */
    protected ?Authenticatable $user = null;

    /**
     * Class Constructor
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Check if guest (unauthenticated user)
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Authenticates a user based on bearer token
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        if($this->user !== null) {
            return $this->user;
        }

        $token = $this->request->bearerToken();
        if($token === null) {
            return null;
        }

        $this->user = $this->authenticateUsingBearerToken($token);

        return $this->user;
    }

    /**
     * Gets identifier for the user
     * @return mixed|null
     */
    public function id()
    {
        if($user = $this->user()) {
            return $user->getAuthIdentifier();
        }

        return null;
    }

    /**
     * Validates credentials
     * Token key must be defined
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        if(!isset($credentials['token'])) {
            return false;
        }

        return  $this->authenticateUsingBearerToken($credentials['token']) !== null;
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $user;
    }

    /**
     * Authenticates and validates a jwt token
     * @param string $token
     * @return Authenticatable|null
     */
    protected function authenticateUsingBearerToken(string $token): ?Authenticatable
    {
        if(!$token = Identity::validateAccessToken($token)) {
            return null;
        }

        return $this->provider->retrieveById($token->claims()->get('sub'));
    }

}