<?php

namespace Zploited\Identity\Client\Laravel\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Session;
use Zploited\Identity\Client\Laravel\Models\Token;

class SessionGuard implements Guard
{
    protected ?UserProvider $provider = null;

    protected ?Authenticatable $user = null;

    protected string $sessionName;

    public function __construct(?UserProvider $provider, string $name)
    {
        $this->provider = $provider;
        $this->sessionName = $name;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): Authenticatable|null
    {
        /*
         * Checking if the user has already been set,
         * if he is we can just return the user
         */
        if($this->user !== null) {
            return $this->user;
        }

        /*
         * Gets the token from a cookie and stores the token locally
         * if this cookie isn't set it means none is logged in, and we can return null
         */
        /** @var string|null $serializedToken */
        $serializedToken = Session::get($this->sessionName);
        if($serializedToken === null) {
            return null;
        }

        /** @var Token $token */
        $token = unserialize($serializedToken);
        $this->user = $token;

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
        if($user instanceof Token) {
            Session::put($this->sessionName, serialize($user));

            $this->user = $user;
        }
    }
}
