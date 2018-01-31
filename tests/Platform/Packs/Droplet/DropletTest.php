<?php

namespace Tests\SuperV\Platform\Packs\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Droplets\Sample\SampleDroplet;
use SuperV\Droplets\Sample\SampleDropletServiceProvider;
use SuperV\Platform\Packs\Droplet\Droplet;
use SuperV\Platform\Packs\Droplet\DropletModel;
use SuperV\Platform\Packs\Droplet\ServiceProvider as DropletServiceProvider;
use Tests\SuperV\Platform\BaseTestCase;

class DropletTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function creates_droplet_instance()
    {
        $this->setUpDroplet();

        $entry = DropletModel::bySlug('superv.droplets.sample');
        $droplet = $entry->resolveDroplet();

        $this->assertInstanceOf(Droplet::class, $droplet);
        $this->assertInstanceOf(SampleDroplet::class, $droplet);
        $this->assertEquals($entry, $droplet->entry());
    }

    /**
     * @test
     */
    function creates_service_provider_instance()
    {
        $this->setUpDroplet();

        $entry = DropletModel::bySlug('superv.droplets.sample');
        $droplet = $entry->resolveDroplet();
        $provider = $droplet->resolveProvider();

        $this->assertInstanceOf(DropletServiceProvider::class, $provider);
        $this->assertInstanceOf(SampleDropletServiceProvider::class, $provider);

        $this->assertEquals($droplet, $provider->droplet());
    }
}