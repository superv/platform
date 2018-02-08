<?php

namespace Tests\SuperV\Platform\Providers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Exceptions\DropletNotFoundException;
use SuperV\Platform\Packs\Droplet\Installer;
use SuperV\Platform\Packs\Port\PortDetectedEvent;
use SuperV\Platform\Providers\ThemeServiceProvider;
use Tests\SuperV\Platform\BaseTestCase;

class ThemeServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @testÖ
     */
    function get_registered_with_platform()
    {
        $this->assertProviderRegistered(ThemeServiceProvider::class);
    }

    /** @test */
    function adds_theme_view_hint_for_the_active_theme_when_port_is_detected()
    {
        app(Installer::class)
            ->path('tests/Platform/__fixtures__/starter-theme')
            ->slug('themes.starter')
            ->install();

        $this->setUpPort('web', 'superv.io', 'themes.starter');

        PortDetectedEvent::dispatch('web');

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains(base_path('tests/Platform/__fixtures__/starter-theme/resources/views'), $hints['theme']);
        $this->assertDirectoryExists(reset($hints['theme']));
    }

    /** @test */
    function does_not_add_any_hint_if_port_has_no_theme()
    {
        $this->setUpPort('web', 'superv.io', $theme = null);

        PortDetectedEvent::dispatch('web');

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertFalse(array_key_exists('theme', $hints));
    }

    /** @test */
    function throws_exception_if_ports_theme_is_not_found()
    {
        $this->expectException(DropletNotFoundException::class);

        $this->setUpPort('web', 'superv.io', $theme = 'non.existant.theme');

        PortDetectedEvent::dispatch('web');
    }
}