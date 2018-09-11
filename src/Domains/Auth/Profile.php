<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'user_profiles';

    protected $guarded = [];
}