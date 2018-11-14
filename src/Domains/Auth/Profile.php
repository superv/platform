<?php

namespace SuperV\Platform\Domains\Auth;

use SuperV\Platform\Domains\Database\Model\Entry;

class Profile extends Entry
{
    protected $table = 'user_profiles';

    protected $guarded = [];
}