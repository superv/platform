<?php

namespace Tests\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use SuperV\Platform\Domains\Routing\Router;
use Tests\Platform\BaseTestCase;

class DropletServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function registers_droplets_routes_from_routes_folder()
    {
        $router = $this->app->instance(Router::class, Mockery::mock(Router::class));
        $router->shouldReceive('loadFromPath')
               ->with('tests/Platform/__fixtures__/sample-droplet/routes')
               ->once();

        $this->setUpDroplet();
    }

    /** @test */
    function adds_droplets_view_namespaces()
    {
        $droplet = $this->setUpDroplet();

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains(base_path($droplet->resourcePath('views')), $hints['droplets.sample']);
        $this->assertDirectoryExists(reset($hints['droplets.sample']));
    }

    /** @test */
    function registers_migrations_path()
    {
        $droplet = $this->setUpDroplet();

        $this->assertEquals($droplet->path('database/migrations'), config('superv.migrations.scopes')[$droplet->slug()]);
    }
}