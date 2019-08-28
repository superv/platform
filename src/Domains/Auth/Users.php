<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Auth\Contracts\Users as UsersContract;

class Users implements UsersContract
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    /** @var \SuperV\Platform\Domains\Auth\Contracts\User|\SuperV\Platform\Domains\Database\Model\Model  */
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function count()
    {
        return $this->query()->count();
    }

    public function first()
    {
        return $this->query()->first();
    }

    public function withEmail($email): ?User
    {
        return $this->query()->whereEmail($email)->first();
    }

    public function find($id, $columns = ['*'])
    {
        return $this->query()->find($id, $columns);
    }

    public function create(array $attributes = [])
    {
        return $this->query()->create($attributes);
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