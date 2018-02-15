<?php

namespace SuperV\Platform\Domains\Auth;

interface Users
{
    public function count();

    public function first();

    public function create(array $attributes = []);
}