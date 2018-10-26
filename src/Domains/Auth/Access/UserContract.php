<?php

namespace SuperV\Platform\Domains\Auth\Access;

interface UserContract extends \SuperV\Platform\Domains\Auth\Contracts\User
{
    public function assign(string $role);

    public function isA($role);

    public function isAn($role);

    public function isNotA($role);

    public function isNotAn($role);
}