<?php

namespace Zploited\Laravel\Identity\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Zploited\Laravel\Identity\Identity;
use Zploited\Laravel\Identity\Models\AccessToken;

class TokenUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        $token = new AccessToken(request()->bearerToken());
        if($identifier === $token->sub) {
            return $token;
        }

        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        return null;
    }

    public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if(!array_key_exists('token', $credentials)) {
            return false;
        }

        return Identity::validateAccessToken($credentials['token']);
    }
}