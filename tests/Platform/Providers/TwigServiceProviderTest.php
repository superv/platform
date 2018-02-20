<?php

namespace Tests\Platform\Providers;

use SuperV\Platform\Providers\TwigServiceProvider;
use Tests\Platform\BaseTestCase;
use TwigBridge\ServiceProvider as TwigBridgeServiceProvider;

class TwigServiceProviderTest extends BaseTestCase
{
    /** @test */
    function is_registered_if_enabled_by_config()
    {
        config()->set('superv.twig.enabled', true);

        $this->assertProviderRegistered(TwigServiceProvider::class);
        $this->assertProviderRegistered(TwigBridgeServiceProvider::class);
    }
}