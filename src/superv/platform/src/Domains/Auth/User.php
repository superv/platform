<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Contracts\User as UserContract;

class User extends Model implements UserContract, AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'users';

    protected $guarded = [];

    protected $casts = [
        'ports' => 'json'
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function createProfile(array $attributes)
    {
        return $this->profile()->create($attributes);
    }
}