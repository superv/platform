<?php
namespace Tests\Platform\Domains\Resource\Fixtures\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ResourceExtension;

class TestAExtension implements ResourceExtension
{
    public function extends()
    {
        return 'test_a';
    }
}