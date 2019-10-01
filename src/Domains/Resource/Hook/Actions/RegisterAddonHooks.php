<?php

namespace SuperV\Platform\Domains\Resource\Hook\Actions;

use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Resource\Hook\HookManager;

class RegisterAddonHooks
{
    public function handle(AddonBootedEvent $event)
    {
        $hooksPath = $event->addon->realPath('src/Resources');
        if (! file_exists($hooksPath)) {
            return;
        }

        HookManager::resolve()->scan($hooksPath);
    }
}