<?php

namespace SuperV\Platform\Domains\Addon\Listeners;

use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;

class AddonInstalledListener
{
    public function handle(AddonInstalledEvent $event)
    {
        $addon = $event->addon;

        superv('addons')->put($addon->getNamespace(), $addon);

        $addon->boot();

        $addon->fire('installed');
    }
}
