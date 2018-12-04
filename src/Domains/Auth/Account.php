<?php

namespace SuperV\Platform\Domains\Auth;

use SuperV\Platform\Domains\Auth\Contracts\Account as AccountContract;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class Account extends ResourceEntry implements AccountContract
{
    protected $table = 'sv_accounts';

    public function users()
    {
        return $this->hasMany(config('superv.auth.user.model'));
    }

    public function getId()
    {
        return $this->id;
    }
}