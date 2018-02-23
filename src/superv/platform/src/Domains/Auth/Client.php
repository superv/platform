<?php

namespace SuperV\Platform\Domains\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model implements Authenticatable
{
    use HasUser;

    protected $guarded = [];
}