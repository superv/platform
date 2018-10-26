<?php

namespace SuperV\Platform\Domains\Auth\Access;

class UserObserver
{
    public function deleting(User $user)
    {
        $user->roles()->sync([]);
        $user->actions()->sync([]);

        optional($user->profile)->delete();
    }
}