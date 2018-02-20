<?php

namespace Tests\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Platform;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Events\PlatformBootedEvent;

class PlatformTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function registers_service_providers_for_enabled_droplets()
    {
        $this->setUpDroplet();

        $entry = DropletModel::bySlug('droplets.sample');

        Platform::boot();

        $this->assertContains($entry->resolveDroplet()->providerClass(), array_keys(app()->getLoadedProviders()));
    }

    /** @test */
    function dispatches_event_when_platform_is_booted()
    {
        Event::fake();

        Platform::boot();

        Event::assertDispatched(PlatformBootedEvent::class);
    }

    /** @test */
    function gets_config_from_superv_namespace()
    {
        config(['superv.foo' => 'bar']);
        config(['superv.ping' => 'pong']);

        $this->assertEquals('bar', Platform::config('foo'));
        $this->assertEquals('pong', Platform::config('ping'));
        $this->assertEquals('zone', Platform::config('zoom', 'zone'));
    }

    /** @test */
    function listens_port_detected_event_and_sets_active_port()
    {
        PortDetectedEvent::dispatch(Port::fromSlug('acp'));

        $this->assertEquals('acp', Platform::port()->slug());
    }

    /** @test */
    function returns_platform_relative_path()
    {
        $this->assertEquals('__workbench__/superv/platform', Platform::path());
        $this->assertEquals('__workbench__/superv/platform/resources', Platform::path('resources'));
    }

    /** @test */
    function returns_platform_full_path()
    {
        $this->assertEquals(base_path('__workbench__/superv/platform'), Platform::fullPath());
        $this->assertEquals(base_path('__workbench__/superv/platform/resources'), Platform::fullPath('resources'));
    }
}