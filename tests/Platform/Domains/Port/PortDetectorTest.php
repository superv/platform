<?php

namespace Tests\SuperV\Platform\Domains\Port;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use SuperV\Platform\Domains\Port\PortDetector;
use Tests\SuperV\Platform\BaseTestCase;

class PortDetectorTest extends BaseTestCase
{
    /**
     * @test
     */
    function detects_active_port_from_request()
    {
        $this->setUpPorts();

        $this->assertEquals('web', $this->app->make(PortDetector::class)->detectFor('superv.io', 'foo/bar'));
        $this->assertEquals('web', $this->app->make(PortDetector::class)->detectFor('superv.io', '/'));
        $this->assertEquals('web', $this->app->make(PortDetector::class)->detectFor('superv.io', ''));

        $this->assertEquals('acp', $this->app->make(PortDetector::class)->detectFor('superv.io', 'acp/foo/bar'));
        $this->assertEquals('acp', $this->app->make(PortDetector::class)->detectFor('superv.io', '/acp/foo'));
        $this->assertEquals('acp', $this->app->make(PortDetector::class)->detectFor('superv.io', '/acp'));
        $this->assertEquals('acp', $this->app->make(PortDetector::class)->detectFor('superv.io', 'acp'));

        $this->assertEquals('api', $this->app->make(PortDetector::class)->detectFor('api.superv.io', '/'));
        $this->assertEquals('api', $this->app->make(PortDetector::class)->detectFor('api.superv.io', '/acp'));
        $this->assertEquals('api', $this->app->make(PortDetector::class)->detectFor('api.superv.io', '/acp/foo/bar'));
        $this->assertEquals('api', $this->app->make(PortDetector::class)->detectFor('api.superv.io', 'foo/bar'));
    }

    /**
     * @test
     */
    function sets_active_port_when_a_route_is_matched()
    {
        $this->setUpPorts();

        event(
            new RouteMatched(
                $this->app['router']->get('', 'a@b'),
                Request::create('http://superv.io/foo/bar')
            )
        );

        $this->assertEquals('web', \Platform::activePort());
    }
}