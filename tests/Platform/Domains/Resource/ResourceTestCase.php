<?php

namespace Tests\Platform\Domains\Resource;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;

class ResourceTestCase extends \Tests\Platform\TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    protected $shouldBootPlatform = true;

    protected function tearDown()
    {
        parent::tearDown();
        ResourceFactory::$cache = [];
    }
}
