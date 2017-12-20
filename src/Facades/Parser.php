<?php

namespace SuperV\Platform\Facades;

use Illuminate\Support\Facades\Facade;

class Parser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'superv.parser';
    }
}