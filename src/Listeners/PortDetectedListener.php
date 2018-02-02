<?php

namespace SuperV\Platform\Listeners;

use SuperV\Platform\Packs\Port\PortDetectedEvent;

class PortDetectedListener
{
    public function handle(PortDetectedEvent $event)
    {
        \Platform::setActivePort($event->port);
    }
}