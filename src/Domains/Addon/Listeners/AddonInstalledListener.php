<?php

namespace SuperV\Platform\Domains\Addon\Listeners;

use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;

class AddonInstalledListener
{
    public function handle(AddonInstalledEvent $event)
    {
        $addon = $event->addon;

        superv('addons')->put($addon->slug(), $addon);
        if (method_exists($addon, 'postInstall')) {
            $addon->postInstall();
        }
    }
}