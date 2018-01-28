<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'auth_users';

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function profile() {
        return $this->hasOne(Profile::class);
    }
}