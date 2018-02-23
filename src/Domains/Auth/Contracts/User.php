<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

interface User
{
    public static function query();

    public function createProfile(array $attributes);
}