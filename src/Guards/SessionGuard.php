<?php

namespace Zploited\Identity\Client\Laravel\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Session;
use Zploited\Identity\Client\Exceptions\IdentityCoreException;
use Zploited\Identity\Client\Laravel\Models\Token;

class SessionGuard implements Guard
{
    protected ?UserProvider $provider = null;

    protected ?Authenticatable $token = null;

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

    /**
     * @throws IdentityCoreException
     */
    public function user(): Authenticatable|null
    {
        /*
         * Checking if the user has already been set,
         * if he is we can just return the user
         */
        if($this->token !== null) {
            return $this->token;
        }

        /*
         * Gets the token from a cookie and stores the token locally
         * if this cookie isn't set it means none is logged in, and we can return null
         */
        /** @var string|null $serializedToken */
        $jwt = Session::get($this->sessionName);
        if($jwt === null) {
            return null;
        }

        $token = new Token($jwt);
        $this->token = $token;

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
            Session::put($this->sessionName, $user->getJwtString());

            $this->token = $user;
        }
    }

    public function logout(): void
    {
        Session::remove($this->sessionName);
        $this->token = null;
    }
}
