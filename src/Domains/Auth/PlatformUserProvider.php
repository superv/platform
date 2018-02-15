<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Str;

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

        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        if (! $user = $query->first()) {
            return null;
        }

        if ($port = \Platform::port()) {
            if (! in_array($port->slug(), $user->ports)) {
                return null;
            }
        }

        return $user;
    }
}