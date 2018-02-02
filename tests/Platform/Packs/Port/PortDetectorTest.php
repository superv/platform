<?php

namespace Tests\SuperV\Platform\Packs\Port;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use SuperV\Platform\Packs\Port\PortDetectedEvent;
use SuperV\Platform\Packs\Port\PortDetector;
use Tests\SuperV\Platform\BaseTestCase;

class PortDetectorTest extends BaseTestCase
{
    /**
     * @test
     */
    function detects_active_port_from_request()
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

    /**
     * @test
     */
    function dispatches_event_when_active_port_detected()
    {
        $this->setUpPorts();

        Event::fake([PortDetectedEvent::class]);

        event(
            new RouteMatched(
                $this->app['router']->get('', 'a@b'),
                Request::create('http://superv.io/foo/bar')
            )
        );

        Event::assertDispatched(PortDetectedEvent::class, function($event) {
            return $event->port == 'web';
        });
    }


    /**
     * @return mixed|\SuperV\Platform\Packs\Port\PortDetector
     */
    public function setUpDetector()
    {
        return $this->app->make(PortDetector::class);
    }
}