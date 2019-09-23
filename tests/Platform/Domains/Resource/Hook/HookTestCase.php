<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\HookManager;
use Tests\Platform\Domains\Resource\ResourceTestCase;

abstract class HookTestCase extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        HookManager::resolve()->scan(__DIR__.'/../Fixtures/Resources');
    }

    protected function tearDown()
    {
        HookManager::resolve()->flush();

        parent::tearDown();
    }
}
