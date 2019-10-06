<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterCreatedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterDeletedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterRetrievedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterSavedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\BeforeCreatingHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\BeforeSavingHook;

class ObserverHookHandler extends HookHandler
{
    protected $map = [
        'creating'  => BeforeCreatingHook::class,
        'created'   => AfterCreatedHook::class,
        'saving'    => BeforeSavingHook::class,
        'saved'     => AfterSavedHook::class,
        'retrieved' => AfterRetrievedHook::class,
        'deleted'   => AfterDeletedHook::class,
    ];

    protected $hookType = 'entry';

    protected static $locks = [];
}
