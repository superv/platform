<?php

namespace SuperV\Platform\Listeners;

use SuperV\Platform\Domains\Port\PortDetector;

class RouteMatchedListener
{
    /**
     * @var \SuperV\Platform\Domains\Port\PortDetector
     */
    protected $detector;

    public function __construct(PortDetector $detector)
    {
        $this->detector = $detector;
    }
    public function handle($event)
    {
        $this->detector->detect($event->request);
    }
}