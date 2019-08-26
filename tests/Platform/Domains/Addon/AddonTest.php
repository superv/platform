<?php

namespace Tests\Platform\Domains\Addon;

use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Addons\Sample\SampleAddon;
use SuperV\Addons\Sample\SampleAddonServiceProvider;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\AddonServiceProvider;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use Tests\Platform\TestCase;

class AddonTest extends TestCase
{
    use RefreshDatabase;

    function test__creates_addon_instance()
    {
        $this->setUpAddon();

        $entry = AddonModel::byNamespace('superv.addons.sample');
        $addon = $entry->resolveAddon();

        $this->assertInstanceOf(Addon::class, $addon);
        $this->assertInstanceOf(SampleAddon::class, $addon);
        $this->assertEquals($entry, $addon->entry());
    }

    function test__creates_service_provider_instance()
    {
        $this->setUpAddon();

        $entry = AddonModel::byNamespace('superv.addons.sample');
        $addon = $entry->resolveAddon();
        $provider = $addon->resolveProvider();

        $this->assertInstanceOf(AddonServiceProvider::class, $provider);
        $this->assertInstanceOf(SampleAddonServiceProvider::class, $provider);

        $this->assertEquals($addon, $provider->addon());
    }

    function test__dispatches_event_when_booted()
    {
        $this->setUpAddon();
        $entry = AddonModel::byNamespace('superv.addons.sample');
        $addon = $entry->resolveAddon();

        Event::fake(AddonBootedEvent::class);

        $addon->boot();

        Event::assertDispatched(AddonBootedEvent::class, function (AddonBootedEvent $event) use ($addon) {
            return $addon->getNamespace() === $event->addon->getNamespace();
        });
    }
}
