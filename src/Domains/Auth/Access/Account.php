<?php

namespace SuperV\Platform\Domains\Auth\Access;

use SuperV\Modules\Nucleo\Domains\Entry\Entry;
use SuperV\Platform\Domains\Auth\Contracts\Account as AccountContract;

class Account extends Entry implements AccountContract
{
    protected $table = 'accounts';

    public function users()
    {
        return $this->hasMany(config('superv.auth.user.model'));
    }
}