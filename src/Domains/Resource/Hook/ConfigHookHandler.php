<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandler as HookContract;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class ConfigHookHandler implements HookContract
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $eventName = sprintf("%s::config.resolved", $identifier);
        $this->dispatcher->listen($eventName, function ($payload) use ($hookHandler) {
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
