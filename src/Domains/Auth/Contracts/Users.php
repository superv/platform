<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

interface Users
{
    public function count();

    public function first();

    /**
     * @param array $attributes
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    public function create(array $attributes = []);
}