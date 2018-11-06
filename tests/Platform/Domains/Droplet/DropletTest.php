<?php

namespace Tests\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Addons\Sample\SampleAddon;
use SuperV\Addons\Sample\SampleAddonServiceProvider;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\AddonServiceProvider;
use Tests\Platform\TestCase;

class DropletTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_droplet_instance()
    {
        $this->setUpDroplet();

        $entry = AddonModel::bySlug('superv.addons.sample');
        $droplet = $entry->resolveDroplet();

        $this->assertInstanceOf(Addon::class, $droplet);
        $this->assertInstanceOf(SampleAddon::class, $droplet);
        $this->assertEquals($entry, $droplet->entry());
    }

    /** @test */
    function creates_service_provider_instance()
    {
        $this->setUpDroplet();

        $entry = AddonModel::bySlug('superv.addons.sample');
        $droplet = $entry->resolveDroplet();
        $provider = $droplet->resolveProvider();

        $this->assertInstanceOf(AddonServiceProvider::class, $provider);
        $this->assertInstanceOf(SampleAddonServiceProvider::class, $provider);

        $this->assertEquals($droplet, $provider->droplet());
    }
}