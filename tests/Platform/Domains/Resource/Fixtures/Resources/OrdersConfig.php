<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\ResourceConfig;

class OrdersConfig
{
    public static $identifier = 'testing.orders';

    public function resolved(ResourceConfig $config)
    {
        $config->label('Orders Hooked');
    }
}
