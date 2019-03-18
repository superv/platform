<?php

namespace Tests\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Platform;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Events\PlatformBootedEvent;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    function test_registers_service_providers_for_enabled_addons()
    {
        $this->setUpAddon();

        $entry = AddonModel::bySlug('superv.addons.sample');

        Platform::boot();

        $this->assertContains($entry->resolveAddon()->providerClass(), array_keys(app()->getLoadedProviders()));
    }

    function test_dispatches_event_when_platform_has_booted()
    {
        Event::fake();

        Platform::boot();

        Event::assertDispatched(PlatformBootedEvent::class);
    }

    function test_gets_config_from_superv_namespace()
    {
        config(['superv.foo' => 'bar']);
        config(['superv.ping' => 'pong']);

        $this->assertEquals('bar', Platform::config('foo'));
        $this->assertEquals('pong', Platform::config('ping'));
        $this->assertEquals('zone', Platform::config('zoom', 'zone'));
    }

    function test_listens_port_detected_event_and_sets_active_port()
    {
        $this->setUpPort('acp', 'hostname.io');
        PortDetectedEvent::dispatch(\Hub::get('acp'));

        $this->assertEquals('acp', Platform::port()->slug());
    }

    function test_returns_platform_full_path()
    {
        $this->assertEquals(base_path(), Platform::fullPath());
        $this->assertEquals(base_path('resources'), Platform::fullPath('resources'));
    }
}