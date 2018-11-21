<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ResourceExtension;
use SuperV\Platform\Domains\Resource\Resource;

class TestBExtension implements ResourceExtension
{
    public function extends(): string
    {
        return 'test_b';
    }

    public function extend(Resource $resource)
    {
    }
}