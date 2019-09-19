<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Hook;
use Tests\Platform\Domains\Resource\ResourceTestCase;

abstract class HookTestCase extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Hook::resolve()->scan(__DIR__.'/../Fixtures/Resources');
    }

    protected function tearDown()
    {
        Hook::resolve()->flush();

        parent::tearDown();
    }
}
