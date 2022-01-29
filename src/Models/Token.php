<?php

namespace Zploited\Laravel\Identity\Models;

use Illuminate\Contracts\Auth\Authenticatable;

class Token implements Authenticatable
{
    protected $identifier;
    protected $scopes;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
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

    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}