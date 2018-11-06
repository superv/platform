<?php

namespace Tests\Platform\Domains\Addon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use SuperV\Platform\Domains\Database\Migrations\Scopes;
use SuperV\Platform\Domains\Routing\Router;
use Tests\Platform\TestCase;

class AddonServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function registers_addons_routes_from_routes_folder()
    {
        $router = $this->app->instance(Router::class, Mockery::mock(Router::class));
        $router->shouldReceive('loadFromPath')
               ->with('tests/Platform/__fixtures__/sample-addon/routes')
               ->once();

        $this->setUpAddon();
    }

    /** @test */
    function adds_addons_view_namespaces()
    {
        $addon = $this->setUpAddon();

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains(base_path($addon->resourcePath('views')), $hints['superv.addons.sample']);
        $this->assertDirectoryExists(reset($hints['superv.addons.sample']));
    }

    /** @test */
    function registers_migrations_path()
    {
        $addon = $this->setUpAddon();

        $this->assertEquals(base_path($addon->path('database/migrations')), Scopes::path($addon->slug()));
    }
}