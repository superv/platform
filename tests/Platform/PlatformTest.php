<?php

namespace Tests\SuperV\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Platform;
use SuperV\Platform\Domains\Droplet\DropletModel;

class PlatformTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function installs_droplet_from_a_directory()
    {
        /**
         * 1. verify path
         */
        $this->setUpDroplet();

        $this->assertDatabaseHas('droplets', [
            'name'      => 'sample',
            'slug'      => 'superv.droplets.sample',
            'type'      => 'droplet',
            'path'      => 'tests/Platform/__fixtures__/sample-droplet',
            'enabled'   => true,
            'namespace' => 'SuperV\\Droplets\\Sample',
        ]);
    }

    /**
     * @test
     */
    function registers_service_providers_for_enabled_droplets()
    {
        $this->setUpDroplet();

        $entry = DropletModel::bySlug('superv.droplets.sample');

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
}