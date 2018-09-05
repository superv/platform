<?php

namespace Tests\Platform;

use Illuminate\Foundation\AliasLoader;
use Platform;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\PlatformServiceProvider;

class PlatformServiceProviderTest extends TestCase
{
    /** @test */
    function get_registered_with_platform()
    {
        $this->assertProviderRegistered(PlatformServiceProvider::class);
    }

    /** @test */
    function registers_required_aliases_if_installed()
    {
        config(['superv.installed' => true]);
        (new PlatformServiceProvider($this->app))->register();

        $aliases = AliasLoader::getInstance()->getAliases();
        $this->assertEquals(PlatformFacade::class, $aliases['Platform']);
    }

    /** @test */
    function boots_platform_if_superv_is_installed()
    {
        config(['superv.installed' => true]);

        Platform::shouldReceive('boot')->once();
        Platform::makePartial();
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
