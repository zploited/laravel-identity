<?php

namespace Zploited\Identity\Client\Laravel\Models;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class Token extends \Zploited\Identity\Client\Token implements Authenticatable
{
    /**
     * Get method
     * Redirects property calls to access tokenhandler,
     * to make this object authenticatable.
     *
     * @param string $property
     * @return void
     */
    public function __get(string $property)
    {
        return $this->accessTokenHandler->$property;
    }

    public function getAuthIdentifierName()
    {
        return 'sub';
    }

    public function getAuthIdentifier()
    {
        return $this->accessTokenHandler->sub;
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

    /**
     * Creates an object of this type based off the original Token class
     *
     * @throws Exception
     */
    public static function fromBase(\Zploited\Identity\Client\Token $token): Token
    {
        return new self(
            $token->getAccessToken(),
            $token->expiresIn(),
            $token->getRefreshToken(),
            $token->getIdToken()
        );
    }
}