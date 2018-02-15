<?php

namespace SuperV\Platform\Domains\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Auth\Contracts\User;

class UserCreatedEvent
{
    use Dispatchable;

    /**
     * @var User
     */
    public $user;

    /**
     * @var array
     */
    public $request;

    public function __construct(User $user, array $request)
    {
        $this->user = $user;
        $this->request = $request;
    }
}