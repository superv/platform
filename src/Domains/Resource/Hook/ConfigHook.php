<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\Hook as HookContract;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class ConfigHook implements HookContract
{
    public function hook(string $identifier, string $hookHandler)
    {
        $eventName = sprintf("%s::config.resolved", $identifier);
        app('events')->listen($eventName, function ($payload) use ($hookHandler) {
            $this->handle($hookHandler, $payload);
        });
    }

    protected function handle($hookHandler, $payload)
    {
        /** @var ResourceConfig $payload */
        if (is_string($hookHandler)) {
            $hookHandler = app($hookHandler);
        }

        if (method_exists($hookHandler, 'resolved')) {
            $hookHandler->resolved($payload);
        }
    }
}
