<?php

namespace SuperV\Platform\Facades;

use Illuminate\Support\Facades\Facade;

class Inflator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'superv.inflator';
    }
}