<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Contracts\Account as AccountContract;

class Account extends Model implements AccountContract
{
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(config('superv.auth.user.model'));
    }
}