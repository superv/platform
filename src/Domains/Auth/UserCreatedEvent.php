<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Http\Request;

class UserCreatedEvent
{
    /**
     * @var \SuperV\Platform\Domains\Auth\User
     */
    public $user;

    /**
     * @var \Illuminate\Http\Request
     */
    public $request;

    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }
}