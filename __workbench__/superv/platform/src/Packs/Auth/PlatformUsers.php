<?php

namespace SuperV\Platform\Packs\Auth;

class PlatformUsers implements Users
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    public function __construct(User $user)
    {
        $this->query = $user->query();
    }

    public function count()
    {
        return $this->query->count();
    }

    public function first()
    {
        return $this->query->first();
    }

    public function create(array $attributes = [])
    {
        return $this->query->create($attributes);
    }
}