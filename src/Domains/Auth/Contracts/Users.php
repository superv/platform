<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

interface Users
{
    public function count();

    public function first();

    public function withEmail($email): ?User;

    public function find($id, $columns = ['*']);

    /**
     * @param array $attributes
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    public function create(array $attributes = []);
}