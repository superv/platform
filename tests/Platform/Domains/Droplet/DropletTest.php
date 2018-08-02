<?php

namespace Tests\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Droplets\Sample\SampleDroplet;
use SuperV\Droplets\Sample\SampleDropletServiceProvider;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Droplet\DropletServiceProvider as DropletServiceProvider;
use Tests\Platform\TestCase;

class DropletTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_droplet_instance()
    {
        $this->setUpDroplet();

        $entry = DropletModel::bySlug('superv.droplets.sample');
        $droplet = $entry->resolveDroplet();

        $this->assertInstanceOf(Droplet::class, $droplet);
        $this->assertInstanceOf(SampleDroplet::class, $droplet);
        $this->assertEquals($entry, $droplet->entry());
    }

    /** @test */
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