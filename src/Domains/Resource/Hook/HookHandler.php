<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandlerInterface;

abstract class HookHandler implements HookHandlerInterface
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    protected function registerListener($eventName, $eventType, $handler)
    {
        $this->dispatcher->listen(
            $eventName,
            function () use ($eventType, $handler) {
                $this->handle($handler, $eventType, func_get_args());
            }
        );
    }

    protected function handle($hookHandler, $eventType, $payload)
    {
        if (is_string($hookHandler)) {
            $hookHandler = app($hookHandler);
        }

        $hookHandler->{camel_case($eventType)}(...$payload);
    }
}
