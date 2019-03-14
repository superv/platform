<?php

namespace Tests\Platform\Domains\Port;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Domains\Port\PortDetector;
use Tests\Platform\TestCase;

class PortDetectorTest extends TestCase
{
    use RefreshDatabase;

    function test_detects_active_port_from_request()
    {
        $this->setUpPorts();

        $this->assertNull($this->setUpDetector()->detectFor('other.io', 'foo/bar'));

        $this->assertEquals('web', $this->setUpDetector()->detectFor('superv.io', 'foo/bar'));
        $this->assertEquals('web', $this->setUpDetector()->detectFor('superv.io', '/'));
        $this->assertEquals('web', $this->setUpDetector()->detectFor('superv.io', ''));

        $this->assertEquals('acp', $this->setUpDetector()->detectFor('superv.io', 'acp/foo/bar'));
        $this->assertEquals('acp', $this->setUpDetector()->detectFor('superv.io', '/acp/foo'));
        $this->assertEquals('acp', $this->setUpDetector()->detectFor('superv.io', '/acp'));
        $this->assertEquals('acp', $this->setUpDetector()->detectFor('superv.io', 'acp'));

        $this->assertEquals('api', $this->setUpDetector()->detectFor('api.superv.io', '/'));
        $this->assertEquals('api', $this->setUpDetector()->detectFor('api.superv.io', '/acp'));
        $this->assertEquals('api', $this->setUpDetector()->detectFor('api.superv.io', '/acp/foo/bar'));
        $this->assertEquals('api', $this->setUpDetector()->detectFor('api.superv.io', 'foo/bar'));
    }

    function test__does_not_detect_for_same_hostname_with_different_prefix()
    {
        $this->setUpCustomPort('superv.io', 'sv-api');

        $this->assertNull($this->setUpDetector()->detectFor('superv.io', 'other-api'));
    }

    function test_dispatches_event_when_active_port_detected()
    {
        $this->setUpPorts();

        Event::fake([PortDetectedEvent::class]);

        event(
            new RouteMatched(
                $this->app['router']->get('', 'a@b'),
                Request::create('http://superv.io/foo/bar')
            )
        );

        Event::assertDispatched(PortDetectedEvent::class, function ($event) {
            return $event->port->slug() === 'web';
        });
    }

    /**
     * @return mixed|\SuperV\Platform\Domains\Port\PortDetector
     */
    protected function setUpDetector()
    {
        return $this->app->make(PortDetector::class);
    }
}