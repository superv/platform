<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Auth\Concerns\HasUser;
use SuperV\Platform\Domains\Auth\Contracts\HasUser as HasUserContract;

class Client extends Model implements Authenticatable, HasUserContract
{
    use HasUser;

    protected $guarded = [];
}