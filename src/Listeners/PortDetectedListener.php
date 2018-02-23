<?php

namespace SuperV\Platform\Listeners;

use SuperV\Platform\Domains\Port\PortDetectedEvent;

class PortDetectedListener
{
    public function handle(PortDetectedEvent $event)
    {
        \Platform::setPort($event->port);
    }
}