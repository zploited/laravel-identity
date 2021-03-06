<?php

namespace Zploited\Laravel\Identity\Auth;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Configuration;
use Zploited\Laravel\Identity\Identity;
use Zploited\Laravel\Identity\Models\BearerToken;

class CookieGuard implements Guard
{
    protected ?UserProvider $provider = null;

    protected ?Authenticatable $user = null;

    protected string $cookieName;

    public function __construct(?UserProvider $provider, string $name)
    {
        $this->provider = $provider;
        $this->cookieName = $name;
    }

    public function check(): bool
    {
        return !is_null($this->user());
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?Authenticatable
    {
        if($this->user !== null) {
            return $this->user;
        }

        $bearerToken = Cookie::get($this->cookieName);
        if($bearerToken === null) {
            return null;
        }

        $jwt = Identity::validateAccessToken($bearerToken);
        if($jwt === null) {
            return null;
        }

        if($this->provider === null) {
            $this->user = new BearerToken($bearerToken);
        } else {
            $user = $this->provider->retrieveById($jwt->claims()->get('sub'));
            if(method_exists($user, 'setBearerToken')) {
                $user->setBearerToken($bearerToken);
            }

            $this->user = $user;
        }

        return $this->user;
    }

    public function id()
    {
        if($user = $this->user()) {
            return $user->getAuthIdentifier();
        }

        return null;
    }

    public function validate(array $credentials = []): bool
    {
        if(!isset($credentials['token'])) {
            return false;
        }

        $bearerToken = Cookie::get($this->cookieName);
        if($bearerToken === null) {
            return false;
        }

        return Identity::validateAccessToken($bearerToken) !== null;
    }

    /**
     * @param Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        if(!$user instanceof BearerToken) {
            $config = Configuration::forUnsecuredSigner();
            $exp = ($config->parser()->parse($user->getBearerToken()))->claims()->get('exp');
        } else {
            $exp = $user->exp->getTimestamp();
        }

        $expires = $exp - now()->getTimestamp();

        Cookie::queue($this->cookieName, $user->getBearerToken(), ($expires / 60));
        $this->user = $user;
    }

    public function login(Authenticatable $user)
    {
        $this->setUser($user);
    }
}