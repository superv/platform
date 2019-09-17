<?php

namespace SuperV\Platform\Domains\Addon\Listeners;

use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;

class AddonInstalledListener
{
    public function handle(AddonInstalledEvent $event)
    {
        $addon = $event->addon;

        sv_resource('platform::namespaces')->create([
            'namespace' => $addon->getIdentifier().'::resources',
            'type'      => 'resource',
        ]);

        superv('addons')->put($addon->getIdentifier(), $addon);

        $addon->boot();

        $addon->fire('installed');
    }
}
