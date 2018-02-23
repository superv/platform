<?php

namespace SuperV\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use SuperV\Platform\Platform;

class PlatformFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Platform::class;
    }
}