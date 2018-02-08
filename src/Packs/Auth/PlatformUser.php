<?php

namespace SuperV\Platform\Packs\Auth;

use Illuminate\Database\Eloquent\Model;

class PlatformUser extends Model implements User
{
    protected $table = 'users';

    protected $guarded = [];
}