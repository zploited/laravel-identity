<?php

namespace Zploited\Identity\Client\Laravel\Models;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class AccessToken extends \Zploited\Identity\Client\Models\AccessToken implements Authenticatable
{
    public function getAuthIdentifierName(): string
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

    /**
     * Creates an object of this type based off the original AccessToken class
     *
     * @throws Exception
     */
    public static function fromBase(\Zploited\Identity\Client\Models\AccessToken $token): AccessToken
    {
        return new self((string)$token);
    }
}