<?php

namespace Zploited\Laravel\Identity\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;

class AccessToken implements Authenticatable
{
    protected $sub;
    protected ?string $bearerToken = null;
    protected ?Plain $token = null;

    public function __construct($identifier, string $bearerToken = null)
    {
        $this->sub = $identifier;
        $this->bearerToken = $bearerToken;

        if($bearerToken !== null) {
            try {
                $config = Configuration::forUnsecuredSigner();
                $this->token = $config->parser()->parse($bearerToken);

                if($identifier !== $this->token->claims()->get('sub')) {
                    $this->token = null;
                }
            } catch (\Exception $ex) {}
        }
    }

    public function __get($property)
    {
        if($property === 'sub' && $this->token === null) {
            return $this->sub;
        }

        if($this->token !== null) {
            try {
                return $this->token->claims()->get($property);
            } catch (\Exception $exception) {
                return null;
            }
        }

        return null;
    }

    public function getAuthIdentifierName()
    {
        return 'identifier';
    }

    public function getAuthIdentifier()
    {
        return $this->identifier;
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