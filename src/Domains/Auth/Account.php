<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Contracts\Account as AccountContract;

class Account extends Model implements AccountContract
{
    protected $guarded = [];

    protected $table = 'sv_accounts';

    public function users()
    {
        return $this->hasMany(config('superv.auth.user.model'));
    }
}