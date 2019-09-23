<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ConfigResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ResourceResolvedHook;

class ResourceHookHandler extends HookHandler
{
    protected $map = [
        'resolved'        => ResourceResolvedHook::class,
        'config_resolved' => ConfigResolvedHook::class,
    ];

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $implements = class_implements($hookHandler);

        foreach ($this->map as $eventType => $contract) {
            if (! in_array($contract, $implements)) {
                continue;
            }
            $eventName = sprintf("%s.events:%s", $identifier, $eventType);

            $this->registerListener($eventName, $eventType, $hookHandler);
        }
    }
}
