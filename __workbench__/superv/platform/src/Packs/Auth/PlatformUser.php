<?php

namespace SuperV\Platform\Packs\Auth;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
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