<?php

namespace SuperV\Platform\Domains\Auth\Events;

use SuperV\Platform\Domains\Auth\Contracts\User;

class UserCreatedEvent
{
    /**
     * @var User
     */
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}