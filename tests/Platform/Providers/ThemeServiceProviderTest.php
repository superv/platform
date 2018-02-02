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

    /**
     * @test
     */
    function adds_theme_view_hint_for_the_active_theme_when_port_is_detected()
    {
        app(Installer::class)
            ->path('tests/Platform/__fixtures__/starter-theme')
            ->slug('themes.starter')
            ->install();

        config([
            'superv.ports' => [
                'web' => [
                    'hostname' => 'superv.io',
                    'theme'    => 'themes.starter',
                ],
            ],
        ]);

        PortDetectedEvent::dispatch('web');

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains('tests/Platform/__fixtures__/starter-theme/resources/views', $hints['theme']);
    }

    /**
     * @test
     */
    function does_not_add_any_hint_if_port_has_no_theme()
    {
        config([
            'superv.ports' => [
                'web' => [
                    'hostname' => 'superv.io',
                ],
            ],
        ]);

        PortDetectedEvent::dispatch('web');

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertFalse(array_key_exists('theme', $hints));
    }

    /**
     * @test
     */
    function throws_exception_if_ports_theme_is_not_found()
    {
        $this->expectException(DropletNotFoundException::class);

        config([
            'superv.ports' => [
                'web' => [
                    'hostname' => 'superv.io',
                    'theme'    => 'droplet.that.does.not.exist',
                ],
            ],
        ]);

        PortDetectedEvent::dispatch('web');
    }
}