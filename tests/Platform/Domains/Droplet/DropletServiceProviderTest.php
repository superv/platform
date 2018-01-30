<?php

namespace Tests\SuperV\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Droplet\DropletModel;
use Tests\SuperV\Platform\BaseTestCase;

class DropletServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function adds_droplets_view_namespaces()
    {
        $this->setUpDroplet();
        $entry = DropletModel::bySlug('superv.droplets.sample');
        $droplet = $entry->resolveDroplet();
        $provider = $droplet->resolveProvider();

        $this->app->register($provider);

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains($entry->path.'/resources/views', $hints['sample']);
        $this->assertContains($entry->path.'/resources/views', $hints['superv.droplets.sample']);
    }
}