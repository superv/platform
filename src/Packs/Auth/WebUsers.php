<?php

namespace SuperV\Platform\Packs\Auth;

class WebUsers
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    public function __construct(WebUser $user)
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
        $userAttributes = array_pull($attributes, 'user');

        $user = app(Users::class)->create($userAttributes);

        array_set($attributes, 'user_id', $user->id);

        return $this->query->create($attributes);
    }
}