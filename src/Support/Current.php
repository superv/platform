<?php

namespace SuperV\Platform\Support;

use SuperV\Platform\Domains\Auth\Contracts\Users;

class Current
{
    /**
     * @var \SuperV\Platform\Domains\Auth\Contracts\Users
     */
    protected $users;

    protected $user;

    public function __construct(Users $users)
    {
        $this->users = $users;
    }

    /**
     * Return the current logged in user
     *
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    public function user()
    {
        if (! $this->user) {
            $this->user = $this->users->find(auth()->guard()->id());
        }

        return $this->user;
    }

    /**
     * Return the current platform port
     *
     * @return \SuperV\Platform\Domains\Port\Port
     */
    public function port()
    {
        return \Platform::port();
    }

    /**
     * Return current application environment
     *
     * @return string
     */
    public function env()
    {
        return app()->environment();
    }

    /**
     * Return true if we are on local environment
     *
     * @return bool
     */
    public function envIsLocal()
    {
        return $this->env() === 'local';
    }

    /**
     * Return true if we are on testing environment
     *
     * @return bool
     */
    public function envIsTesting()
    {
        return $this->env() === 'testing';
    }

    /**
     * Return true if we are on production environment
     *
     * @return bool
     */
    public function envIsProduction()
    {
        return $this->env() === 'production';
    }

    /**
     * Return true if we are on console
     *
     * @return bool
     */
    public function isConsole()
    {
        return app()->runningInConsole();
    }
}