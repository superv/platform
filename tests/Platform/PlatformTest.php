<?php

namespace Tests\SuperV\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Platform;
use SuperV\Platform\Packs\Droplet\DropletModel;

class PlatformTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function registers_service_providers_for_enabled_droplets()
    {
        $this->setUpDroplet();

        $entry = DropletModel::bySlug('droplets.sample');

        Platform::boot();

        $this->assertContains($entry->resolveDroplet()->providerClass(), array_keys(app()->getLoadedProviders()));
    }

    /**
     * @test
     */
    function gets_config_from_superv_namespace()
    {
        config(['superv.foo' => 'bar']);
        config(['superv.ping' => 'pong']);

        $this->assertEquals('bar', Platform::config('foo'));
        $this->assertEquals('pong', Platform::config('ping'));
        $this->assertEquals('zone', Platform::config('zoom', 'zone'));
    }

    /**
     * @test
     */
    function sets_active_port_when_a_route_is_matched()
    {
        $this->setUpPorts();

        $route = $this->app['router']->get('', 'a@b');

        event(
            new RouteMatched(
                $route,
                $request = Request::create('http://superv.io/foo/bar')
            )
        );
        $this->assertEquals('web', \Platform::activePort());

        event(
            new RouteMatched(
                $route,
                $request = Request::create('http://api.superv.io/foo/bar')
            )
        );
        $this->assertEquals('api', \Platform::activePort());

        event(
            new RouteMatched(
                $route,
                $request = Request::create('http://superv.io/acp/bar')
            )
        );
        $this->assertEquals('acp', \Platform::activePort());
    }

    /**
     * @test
     */
    function returns_platform_relative_path()
    {
        $this->assertEquals('__workbench__/superv/platform', Platform::path());
        $this->assertEquals('__workbench__/superv/platform/resources', Platform::path('resources'));
    }    /**
     * @test
     */

    function returns_platform_full_path()
    {
        $this->assertEquals(base_path('__workbench__/superv/platform'), Platform::fullPath());
        $this->assertEquals(base_path('__workbench__/superv/platform/resources'), Platform::fullPath('resources'));
    }
}