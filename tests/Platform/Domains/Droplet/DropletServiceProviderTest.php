<?php

namespace Tests\SuperV\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\SuperV\Platform\BaseTestCase;

class DropletServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

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