<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Contracts\Account as AccountContract;
use SuperV\Platform\Domains\Auth\Contracts\User;

class Account extends Model implements AccountContract
{
    protected $guarded = [];

    public function users()
    {
        $user = app(User::class);
        return $this->hasMany(get_class($user));
    }
}