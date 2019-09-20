<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Hook\BaseHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterDeletedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterRetrievedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterSavedHook;

class OrdersObserver implements AfterSavedHook, AfterRetrievedHook, AfterDeletedHook
{
    public static $identifier = 'testing.orders';

    public function saved(EntryContract $entry)
    {
        $_SERVER['__hooks::observer.saved'] = [
            'resource' => $entry->getResourceIdentifier(),
            'saved'    => $entry->title === $entry->fresh()->title,
        ];

        // make sure I have the right precautions
        // in order not to lock myself here
        $entry->save();
    }

    public function retrieved(EntryContract $entry)
    {
        $_SERVER['__hooks::observer.retrieved'] = [
            'resource' => $entry->getResourceIdentifier(),
        ];
    }

    public function deleted(EntryContract $entry)
    {
        $_SERVER['__hooks::observer.deleted'] = [
            'resource' => $entry->getResourceIdentifier(),
            'fresh'    => $entry->fresh(),
        ];
    }
}
