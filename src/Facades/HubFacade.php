<?php

namespace SuperV\Platform\Facades;

use Illuminate\Support\Facades\Facade;
use SuperV\Platform\Domains\Port\Hub;

class HubFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
       return Hub::class;
    }
}