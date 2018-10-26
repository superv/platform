<?php

namespace SuperV\Platform\Domains\Auth\Access;

class User extends \SuperV\Platform\Domains\Auth\User implements UserContract
{
    use HasActions;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::observe(UserObserver::class);
    }
}