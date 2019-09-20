<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\Hook as HookContract;

class ListsHook implements HookContract
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [
        'resolved' => null,
    ];

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
            $eventName = sprintf("%s.lists:%s.events:%s", $identifier, $subKey, $eventType);
            $this->dispatcher->listen(
                $eventName,
                function ($payload) use ($eventType, $hookHandler) {
                    $this->handle($hookHandler, $eventType, $payload);
                }
            );
        }
    }

    protected function handle($hookHandler, $eventType, $payload)
    {
        if (is_string($hookHandler)) {
            $hookHandler = app($hookHandler);
        }

        $hookHandler->{$eventType}($payload);
    }
}
