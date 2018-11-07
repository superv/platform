<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Resource\Extension\RegisterExtensionsInPath;

class RegisterExtensions
{
    public function handle(AddonBootedEvent $event)
    {
        RegisterExtensionsInPath::dispatch(
            $event->addon->realPath('src/Extensions'),
            $event->addon->namespace().'\Extensions'
        );
    }
}