<?php

namespace Zploited\Laravel\Identity\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;

class AccessToken implements Authenticatable
{
    protected ?string $bearerToken;
    protected Plain $token;

    public function __construct(string $bearerToken)
    {
        $this->bearerToken = $bearerToken;

        $config = Configuration::forUnsecuredSigner();
        $this->token = $config->parser()->parse($bearerToken);
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
}