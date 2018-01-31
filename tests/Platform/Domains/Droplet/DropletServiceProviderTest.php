<?php

namespace Tests\SuperV\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Routing\Router;
use Tests\SuperV\Platform\BaseTestCase;

class DropletServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function registers_droplets_routes_from_routes_folder()
    {
        $droplet = $this->setUpDroplet();

        $routesPath = $droplet->entry()->path.'/routes';

        $router = $this->setUpMock(Router::class);
        $router->shouldReceive('loadFromPath')->with($routesPath)->once();

        $this->app->register($droplet->resolveProvider());
    }

    /**
     * @test
     */
    function adds_droplets_view_namespaces()
    {
        $droplet = $this->setUpDroplet();
        $provider = $droplet->resolveProvider();

        $this->app->register($provider);

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains($droplet->entry()->path.'/resources/views', $hints['sample']);
        $this->assertContains($droplet->entry()->path.'/resources/views', $hints['superv.droplets.sample']);
    }
}