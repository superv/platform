<?php

namespace SuperV\Platform\Packs\Auth;


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