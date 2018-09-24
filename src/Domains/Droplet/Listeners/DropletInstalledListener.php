<?php

namespace SuperV\Platform\Domains\Droplet\Listeners;

use SuperV\Platform\Domains\Droplet\Events\DropletInstalledEvent;

class DropletInstalledListener
{
    public function handle(DropletInstalledEvent $event)
    {
        if (method_exists($event->droplet, 'postInstall')) {
            $event->droplet->postInstall();
        }
    }
}