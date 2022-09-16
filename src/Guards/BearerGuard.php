<?php

namespace Zploited\Identity\Client\Laravel\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Zploited\Identity\Client\Exceptions\IdentityCoreException;
use Zploited\Identity\Client\Exceptions\IdentityValidationException;
use Zploited\Identity\Client\Laravel\Events\TokenValidationFailed;
use Zploited\Identity\Client\Laravel\Models\Token;
use Zploited\Identity\Client\Validator;

class BearerGuard implements Guard
{
    protected ?UserProvider $provider = null;

    protected ?Authenticatable $token = null;

    protected string $issuer;

    public function __construct(?UserProvider $provider, string $issuer)
    {
        $this->provider = $provider;
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
     * @throws IdentityCoreException
     */
    public function user(): Authenticatable|null
    {
        if($this->token !== null) {
            return $this->token;
        }

        $jwt = request()->bearerToken();
        if($jwt === null) {
            return null;
        }

        $token = new Token($jwt);

        /*
         * We need to validate the token before saving it!
         * We will use the identity validator for that...
         */
        $validator = new Validator($this->issuer);
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
        return;
    }
}