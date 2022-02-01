<?php

namespace Zploited\Laravel\Identity\Auth;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Zploited\Laravel\Identity\Exceptions\AuthenticationException;
use Zploited\Laravel\Identity\Identity;
use Zploited\Laravel\Identity\Models\BearerToken;

class BearerGuard implements Guard
{
    /**
     * @var UserProvider|null
     */
    protected ?UserProvider $provider;

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
     * @param UserProvider|null $provider
     * @param Request $request
     */
    public function __construct(?UserProvider $provider, Request $request)
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

    public function setUser(Authenticatable $user): Authenticatable
    {
        $this->user = $user;

        return $user;
    }

    /**
     * Authenticates and validates a jwt token
     * @param string $bearerToken
     * @return Authenticatable|null
     */
    protected function authenticateUsingBearerToken(string $bearerToken): ?Authenticatable
    {
        if(!$jwt = Identity::validateAccessToken($bearerToken)) {
            return null;
        }

        if($this->provider === null) {
            try {
                $user = new BearerToken($bearerToken);
            } catch (Exception $exception) {
                throw new AuthenticationException('Unable to parse the provided bearer token.');
            }
        } else {
            $user = $this->provider->retrieveById($jwt->claims()->get('sub'));
            if($user === null) {
                throw new AuthenticationException('Provided bearer token does not match a user.');
            }

            if(method_exists($user, 'setBearerToken')) {
                $user->setBearerToken($bearerToken);
            }
        }

        return $user;
    }

}