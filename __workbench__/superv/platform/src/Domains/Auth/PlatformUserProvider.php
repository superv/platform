<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Port\Port;

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
        $port = \Platform::port();
        if ($port->model()) {
            $model = $port->resolveModel();

            $query = $model->newQuery();

            $query->whereHas('user', function($query) use ($credentials, $port) {
                $this->applyFilters($query, $port, $credentials);
            });
        } else {
            $model = $this->createModel();

            $query = $model->newQuery();

            $this->applyFilters($query, $port, $credentials);
        }

        return $query->first();
    }

    protected function applyFilters(Builder $query, Port $port, $credentials)
    {
        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        if ($port) {
            $query->whereIn('type', $port->allowedUserTypes());
        }
    }
}