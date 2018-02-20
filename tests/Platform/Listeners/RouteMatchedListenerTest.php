<?php

namespace Tests\Platform\Listeners;

use Illuminate\Routing\Events\RouteMatched;
use Mockery as m;
use SuperV\Platform\Domains\Port\PortDetector;
use SuperV\Platform\Listeners\RouteMatchedListener;
use Tests\Platform\BaseTestCase;

class RouteMatchedListenerTest extends BaseTestCase
{
    /** @test */
    function invokes_port_detector_when_dispatched()
    {
        $detector = m::mock(PortDetector::class);
        $this->app->singleton(PortDetector::class, function () use($detector) {return $detector;});

        $event = new RouteMatched('route', 'request');
        $listener = $this->app->make(RouteMatchedListener::class);

        $detector->shouldReceive('detect')->with($event->request)->once();
        $listener->handle($event);
    }
}
