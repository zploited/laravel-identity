<?php

namespace Zploited\Laravel\Identity\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Zploited\Laravel\Identity\Exceptions\AuthenticationException;

/**
 * Represents a JWT formed bearer token
 * @property-read string $sub Unique identifier of the user this token is valid for
 */
class BearerToken implements Authenticatable
{
    /**
     * Encoded bearer token in JWT format
     * @var string
     */
    protected string $bearerToken;

    /**
     * Decoded JWT
     * @var Plain
     */
    protected Plain $token;

    /**
     * Class Constructor
     * @throws AuthenticationException
     */
    public function __construct(string $bearerToken)
    {
        $this->bearerToken = $bearerToken;

        try {
            $config = Configuration::forUnsecuredSigner();
            $this->token = $config->parser()->parse($bearerToken);
        } catch (\Exception $ex) {
            throw new AuthenticationException("The provided bearer token is not a parsable Json Web Token");
        }
    }

    public function __get($property)
    {
        try {
            return $this->token->claims()->get($property);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function getAuthIdentifierName()
    {
        return 'sub';
    }

    public function getAuthIdentifier()
    {
        return $this->sub;
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        return;
    }

    public function getRememberTokenName()
    {
        return null;
    }

    public function getBearerToken(): ?string
    {
        return $this->bearerToken;
    }
}