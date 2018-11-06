<?php

namespace Tests\Platform\Domains\Addon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Addons\Sample\SampleAddon;
use SuperV\Addons\Sample\SampleAddonServiceProvider;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\AddonServiceProvider;
use Tests\Platform\TestCase;

class AddonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_addon_instance()
    {
        $this->setUpAddon();

        $entry = AddonModel::bySlug('superv.addons.sample');
        $addon = $entry->resolveAddon();

        $this->assertInstanceOf(Addon::class, $addon);
        $this->assertInstanceOf(SampleAddon::class, $addon);
        $this->assertEquals($entry, $addon->entry());
    }

    /** @test */
    function creates_service_provider_instance()
    {
        $this->setUpAddon();

        $entry = AddonModel::bySlug('superv.addons.sample');
        $addon = $entry->resolveAddon();
        $provider = $addon->resolveProvider();

        $this->assertInstanceOf(AddonServiceProvider::class, $provider);
        $this->assertInstanceOf(SampleAddonServiceProvider::class, $provider);

        $this->assertEquals($addon, $provider->addon());
    }
}