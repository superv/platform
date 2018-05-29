<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

interface User
{
    public static function query();

    public function assign(string $role);

    public function isA($role);

    public function isAn($role);

    public function isNotA($role);

    public function isNotAn($role);

    public function createProfile(array $attributes);

    public function updatePassword($newPassword);
}