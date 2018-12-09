<?php

namespace SuperV\Platform\Domains\Addon\Listeners;

use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;

class AddonBootedListener
{
    public function handle(AddonBootedEvent $event)
    {
        $addon = $event->addon;

        $addon->fire('booted');
    }
}