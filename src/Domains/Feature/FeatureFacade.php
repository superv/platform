<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Support\Facades\Facade;

class FeatureFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FeatureBus::class;
    }
}