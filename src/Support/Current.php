<?php

namespace SuperV\Platform\Support;

use Illuminate\Contracts\Auth\Guard;
use SuperV\Platform\Domains\Auth\Contracts\Users;

class Current
{
    /**
     * @var \SuperV\Platform\Domains\Auth\Contracts\Users
     */
    protected $users;

    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $guard;

    protected $user;

    public function __construct(Users $users, Guard $guard)
    {
        $this->users = $users;
        $this->guard = $guard;
    }

    /**
     * Returns the current logged in user
     *
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    public function user()
    {
        if (! $this->user) {
            $this->user = $this->users->find($this->guard->id());
        }

        return $this->user;
    }

    /**
     * Returns the current platform port
     *
     * @return \SuperV\Platform\Domains\Port\Port
     */
    public function port()
    {
        return \Platform::port();
    }
}