<?php

namespace SuperV\Platform\Domains\Auth;

class Guest extends User
{
    public function isA($role): bool
    {
        return false;
    }

    public function isNotA($role): bool
    {
        return true;
    }

    public function can($action): bool
    {
        return false;
    }

    public function canNot($action): bool
    {
        return true;
    }

    public function canOrFail($action)
    {
        abort(403);
    }

    public function forbidden($action): bool
    {
        return true;
    }
}