<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\PageRenderedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\PageResolvedHook;

class PagesHookHandler extends HookHandler
{
    protected $map = [
        'resolved' => PageResolvedHook::class,
        'rendered' => PageRenderedHook::class,
    ];

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $implements = class_implements($hookHandler);

        foreach ($this->map as $eventType => $contract) {
            if (! in_array($contract, $implements)) {
                continue;
            }
            $eventName = sprintf("%s.pages:%s.events:%s", $identifier, $subKey, $eventType);

            $this->registerListener($eventName, $eventType, $hookHandler);
        }
    }
}
