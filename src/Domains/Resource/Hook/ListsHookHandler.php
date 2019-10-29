<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListDataHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListQueryResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;

class ListsHookHandler extends HookHandler
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [
        'resolved'       => ListResolvedHook::class,
        'data'           => ListDataHook::class,
        'query_resolved' => ListQueryResolvedHook::class,
    ];

    protected $hookType = 'lists';

//    public function hook(string $identifier, string $hookHandler, string $subKey = null)
//    {
//        $implements = class_implements($hookHandler);
//
//        foreach ($this->map as $eventType => $contract) {
//            if (! in_array($contract, $implements)) {
//                continue;
//            }
//            $eventName = sprintf("%s.lists:%s.events:%s", $identifier, $subKey, $eventType);
//            $this->dispatcher->listen(
//                $eventName,
//                function () use ($eventType, $hookHandler) {
//                    $this->handle($hookHandler, $eventType, func_get_args());
//                }
//            );
//        }
//    }
//
//    protected function handle($hookHandler, $eventType, $payload)
//    {
//        if (is_string($hookHandler)) {
//            $hookHandler = app($hookHandler);
//        }
//
//        $hookHandler->{$eventType}(...$payload);
//    }
}
