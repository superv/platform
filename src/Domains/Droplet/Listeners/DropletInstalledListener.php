<?php

namespace SuperV\Platform\Domains\Droplet\Listeners;

use SuperV\Platform\Domains\Droplet\Events\DropletInstalledEvent;

class DropletInstalledListener
{
    public function handle(DropletInstalledEvent $event)
    {
        $droplet = $event->droplet;

        superv('droplets')->put($droplet->slug(), $droplet);
        if (method_exists($droplet, 'postInstall')) {
            $droplet->postInstall();
        }
    }
}