<?php

namespace Tests\Platform;

use Illuminate\Foundation\AliasLoader;
use Platform;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\PlatformServiceProvider;

class PlatformServiceProviderTest extends TestCase
{
    function test__get_registered_with_platform()
    {
        $this->assertProviderRegistered(PlatformServiceProvider::class);
    }

    function test__registers_required_aliases_if_installed()
    {
        config(['superv.installed' => true]);
        (new PlatformServiceProvider($this->app))->register();

        $aliases = AliasLoader::getInstance()->getAliases();
        $this->assertEquals(PlatformFacade::class, $aliases['Platform']);
    }

    function test__boots_platform_if_superv_is_installed()
    {
        config(['superv.installed' => true]);

        Platform::shouldReceive('boot')->once();
        Platform::makePartial();
        $platformServiceProvider = new PlatformServiceProvider($this->app);
        $platformServiceProvider->register();
        $platformServiceProvider->boot();
    }

    function test__does_not_boot_platform_if_superv_is_not_installed()
    {
        config(['superv.installed' => false]);

        Platform::shouldReceive('isInstalled')->andReturn(false);
        Platform::shouldReceive('boot')->never();

        (new PlatformServiceProvider($this->app))->boot();
    }
}
