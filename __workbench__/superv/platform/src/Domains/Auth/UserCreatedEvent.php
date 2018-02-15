<?php

namespace SuperV\Platform\Domains\Auth;


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