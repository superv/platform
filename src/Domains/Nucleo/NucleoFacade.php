<?php

namespace SuperV\Platform\Domains\Nucleo;

use Illuminate\Support\Facades\Facade;

class NucleoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Nucleo::class;
    }
}