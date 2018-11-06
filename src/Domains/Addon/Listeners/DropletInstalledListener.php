<?php

namespace SuperV\Platform\Domains\Addon\Listeners;

use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;

class DropletInstalledListener
{
    public function handle(AddonInstalledEvent $event)
    {
        $droplet = $event->addon;

        superv('addons')->put($droplet->slug(), $droplet);
        if (method_exists($droplet, 'postInstall')) {
            $droplet->postInstall();
        }
    }
}