<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterCreatedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterDeletedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterRetrievedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterSavedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\BeforeCreatingHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\BeforeSavingHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandlerInterface as HookContract;

class ObserverHookHandler implements HookContract
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [
        'creating'  => BeforeCreatingHook::class,
        'created'   => AfterCreatedHook::class,
        'saving'    => BeforeSavingHook::class,
        'saved'     => AfterSavedHook::class,
        'retrieved' => AfterRetrievedHook::class,
        'deleted'   => AfterDeletedHook::class,
    ];

    protected static $locks = [];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $observer = app($hookHandler);

        foreach ($this->map as $eventType => $contract) {
            if (! $observer instanceof $contract) {
                continue;
            }
            $eventName = sprintf("%s::entry.%s", $identifier, $eventType);
            $this->dispatcher->listen($eventName,
                function ($payload) use ($eventType, $hookHandler, $eventName) {
                    $lock = md5($eventName);
                    if (isset(static::$locks[$lock])) {
                        return;
                    }
                    static::$locks[$lock] = true;

                    app($hookHandler)->{$eventType}($payload);

                    unset(static::$locks[$lock]);
                }
            );
        }
    }
}
