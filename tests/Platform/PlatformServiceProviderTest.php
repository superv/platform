<?php

namespace Tests\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Platform;
use SuperV\Platform\PlatformServiceProvider;

class PlatformServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function get_registered_with_platform()
    {
        $this->assertProviderRegistered(PlatformServiceProvider::class);
    }

    /** @test */
    function boots_platform_if_superv_is_installed()
    {
        config(['superv.installed' => true]);

        Platform::shouldReceive('boot')->once();
        (new PlatformServiceProvider($this->app))->boot();
    }

    /** @test */
    function does_not_boot_platform_if_superv_is_not_installed()
    {
        config(['superv.installed' => false]);

        Platform::shouldReceive('boot')->never();
        (new PlatformServiceProvider($this->app))->boot();
    }

}
