<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Auth\Contracts\User as UserContract;

class PlatformUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return null;
        }

        $model = $this->createModel();
        $query = $model->newQuery();

        if (! $model instanceof UserContract) {
            $query->whereHas('user', function ($query) use ($credentials) {
                $this->applyFilters($query, $credentials);
            });
        } else {
            $this->applyFilters($query, $credentials);
        }

        return $query->first();
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();
        $query = $model->newQuery();
        $identifierName = $model->getAuthIdentifierName();

        if (! $model instanceof UserContract) {
            $query->whereHas('user', function ($query) use ($identifier, $identifierName) {
                $query->where($identifierName, $identifier);
            });
        } else {
            $query->where($identifierName, $identifier);
        }

        return $query->first();
    }

    protected function applyFilters(Builder $query, $credentials)
    {
        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        if ($port = \Platform::port()) {
            $query->whereIn('type', $port->allowedUserTypes());
        }
    }
}