<?php

namespace Tests\Platform\Domains\Resource;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;

abstract class ResourceTestCase extends \Tests\Platform\TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    protected $shouldBootPlatform = true;

    protected function tearDown(): void
    {
        parent::tearDown();
        ResourceFactory::$cache = [];
        ResourceConfig::$__cache = [];
        User::$__cache = [];
    }
}
