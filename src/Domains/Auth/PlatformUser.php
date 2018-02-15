<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class PlatformUser extends Model implements User, AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'users';

    protected $guarded = [];

    protected $casts = [
        'ports' => 'json'
    ];
}