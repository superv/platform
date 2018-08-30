<?php

namespace SuperV\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use SuperV\Platform\Support\Current;

class CurrentFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Current::class;
    }
}