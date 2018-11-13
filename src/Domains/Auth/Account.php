<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Contracts\Account as AccountContract;

class Account extends Model implements AccountContract, \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
{
    protected $guarded = [];

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