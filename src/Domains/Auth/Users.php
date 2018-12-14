<?php

namespace SuperV\Platform\Domains\Auth;

use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Auth\Contracts\Users as UsersContract;

class Users implements UsersContract
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    protected $model;

    public function __construct(User $user)
    {
        $this->query = $user->query();
    }

    public function query()
    {
        return $this->query;
    }

    public function count()
    {
        return $this->query->count();
    }

    public function first()
    {
        return $this->query->first();
    }

    public function withEmail($email): ?User
    {
        return $this->query->whereEmail($email)->first();
    }

    public function find($id, $columns = ['*'])
    {
        return $this->query->find($id, $columns);
    }

    public function create(array $attributes = [])
    {
        return $this->query->create($attributes);
    }

    public static function __callStatic($name, $arguments)
    {
        if (starts_with($name, 'with')) {
            $key = snake_case(str_replace('with', '', $name));
            if ($key) {
                return app(static::class)->query()->where($key, $arguments[0])->first();
            }
        }

        throw new \InvalidArgumentException('Unknown method '.$name);
    }
}