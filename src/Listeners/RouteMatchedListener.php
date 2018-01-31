<?php

namespace SuperV\Platform\Listeners;

use SuperV\Platform\Packs\Port\PortDetector;

class RouteMatchedListener
{
    /**
     * @var \SuperV\Platform\Packs\Port\PortDetector
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