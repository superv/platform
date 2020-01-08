<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use Current;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookByRole;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandlerInterface;

abstract class HookHandler implements HookHandlerInterface
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $hookType;

    protected $map = [];

    protected static $locks = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $implements = class_implements($hookHandler);

        foreach ($this->map as $eventType => $contract) {
            if (! in_array($contract, $implements)) {
                continue;
            }
            $eventName = sprintf("%s.%s:%s.events:%s", $identifier, $this->hookType, $subKey, $eventType);

            if (! $subKey) {
                $eventName = str_replace(':.', '.', $eventName);
            }

            $this->registerListener($eventName, $eventType, $hookHandler);
        }
    }

    protected function registerListener($eventName, $eventType, $handler)
    {
        $this->dispatcher->listen($eventName,
            function () use ($eventType, $handler, $eventName) {
                $lock = md5($eventName);
                if (isset(static::$locks[$lock])) {
                    return;
                }
                static::$locks[$lock] = true;

                $this->handle($handler, $eventType, func_get_args());

                unset(static::$locks[$lock]);
            }
        );
    }

    protected function handle($hookHandlerClass, $eventType, $payload)
    {
        $hookHandler = app($hookHandlerClass);

        if ($hookHandler instanceof HookByRole) {
            if (! Current::hasUser()) {
                return;
            }

            if (! Current::user()->isA($hookHandler::getRole())) {
                return;
            }
        }

        $hookHandler->{camel_case($eventType)}(...$payload);
    }
}
