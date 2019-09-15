<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\ResourceConfig;

class OrdersConfig extends ResourceConfig
{
    public static $identifier = 'testing::orders';

    public function getLabel()
    {
        return 'Orders Hooked';
    }
}
