<?php

namespace Zploited\Identity\Client\Laravel\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Session;
use Zploited\Identity\Client\Exceptions\IdentityCoreException;
use Zploited\Identity\Client\Exceptions\IdentityValidationException;
use Zploited\Identity\Client\Identity;
use Zploited\Identity\Client\Laravel\Events\TokenValidationFailed;
use Zploited\Identity\Client\Laravel\Models\Token;
use Zploited\Identity\Client\Validator;

class SessionGuard implements Guard
{
    protected ?UserProvider $provider = null;

    protected ?Authenticatable $token = null;

    protected string $sessionName;

    protected string $issuer;

    public function __construct(?UserProvider $provider, string $name, string $issuer)
    {
        $this->provider = $provider;
        $this->sessionName = $name;
        $this->issuer = $issuer;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * @throws IdentityCoreException|BindingResolutionException
     */
    public function user(): Authenticatable|null
    {
        /*
         * Checking if the user has already been set,
         * if he is we can just return the user
         */
        if($this->token !== null) {
            return $this->token;
        }

        /** @var Identity $identity */
        $identity = app()->make(Identity::class);
        if(!$identity->accessToken()) {
            return null;
        }

        /** @var \Zploited\Identity\Client\Token $origin */
        $origin = $identity->accessToken();
        $token = new Token($origin->getJwtString());

        /*
         * We need to validate the token before saving it!
         * We will use the identity validator for that...
         */
        $validator = new Validator($this->issuer, null, config('identity-client.identity.protocol'));
        try {

            $validator->validateToken($token);

        } catch (IdentityValidationException $validationException) {
            TokenValidationFailed::dispatch($token, $validationException->getMessage());
            return null;
        }

        /*
         * Everything seems fine...
         * Lets store and return it
         */
        $this->token = $token;

        return $token;
    }

    public function id()
    {
        return ($this->user()) ? $this->user()->getAuthIdentifier() : null;
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return $this->user() !== null;
    }

    public function setUser(Authenticatable $user)
    {
        if($user instanceof Token) {
            Session::put($this->sessionName, $user->getJwtString());

            $this->token = $user;
        }
    }

    public function logout(): void
    {
        Session::remove($this->sessionName);
        $this->token = null;
    }
}
