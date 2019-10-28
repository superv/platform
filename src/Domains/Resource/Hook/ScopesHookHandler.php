<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use Current;

class ScopesHookHandler extends HookHandler
{
    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $eventName = sprintf("%s.events:query_resolved", $identifier);

        $this->dispatcher->listen(
            $eventName,
            function () use ($hookHandler, $subKey) {
                $this->handle($hookHandler, $subKey, func_get_args());
            }
        );
    }

    protected function handle($hookHandler, $subKey, $payload)
    {
        if (! Current::hasUser()) {
            return;
        }

        if (! Current::user()->isA($role = $subKey)) {
            return;
        }

        if (is_string($hookHandler)) {
            $hookHandler = app($hookHandler);
        }

        $payload[] = Current::user();

        $hookHandler->scope(...$payload);
    }
}
