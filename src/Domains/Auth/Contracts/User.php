<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface User extends EntryContract
{
    public static function query();

    public function getEmail();

    public function createProfile(array $attributes);

    public function updatePassword($newPassword);

    public function assign(string $role);

    public function isA($role);

    public function isAn($role);

    public function isNotA($role);

    public function isNotAn($role);
}