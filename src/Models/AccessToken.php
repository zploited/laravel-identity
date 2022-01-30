<?php

namespace Zploited\Laravel\Identity\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;

class AccessToken implements Authenticatable
{
    public string $jwt;
    protected Plain $token;

    public function __construct($token)
    {
        $this->jwt = $token;

        $config = Configuration::forUnsecuredSigner();
        $this->token = $config->parser()->parse($token);
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
        return 'jwt';
    }

    public function getAuthIdentifier()
    {
        return $this->jwt;
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