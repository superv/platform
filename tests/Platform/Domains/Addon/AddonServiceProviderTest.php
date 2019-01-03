<?php

namespace Tests\Platform\Domains\Addon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Migrations\Scopes;
use SuperV\Platform\Domains\Routing\Router;
use Tests\Platform\Providers\TestEvent;
use Tests\Platform\Providers\TestListener;
use Tests\Platform\TestCase;

class AddonServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    function test_registers_addons_routes_from_routes_folder()
    {
//        $router = $this->app->instance(Router::class, Mockery::mock(Router::class));
        $router = $this->mock(Router::class);

        $path = $this->basePath('__fixtures__/sample-addon/routes');
        $router->shouldReceive('loadFromPath')
               ->with($path)
               ->once();

        $this->setUpAddon();
    }

    function test__registers_event_listeners_from_config_folder()
    {
        $_SERVER['__addon.listeners.file'] = [TestEvent::class => TestListener::class];
        unset($_SERVER['__event.class']);

        $this->setUpAddon();

        $this->app['events']->fire(new TestEvent());
        $this->assertEquals(TestEvent::class, $_SERVER['__event.class']);
    }

    function test_adds_addons_view_namespaces()
    {
        $addon = $this->setUpAddon();

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains(base_path($addon->resourcePath('views')), $hints['superv.addons.sample']);
        $this->assertDirectoryExists(reset($hints['superv.addons.sample']));
    }

    function test_registers_migrations_path()
    {
        $addon = $this->setUpAddon();

        $this->assertEquals(base_path($addon->path('database/migrations')), Scopes::path($addon->slug()));
    }
}