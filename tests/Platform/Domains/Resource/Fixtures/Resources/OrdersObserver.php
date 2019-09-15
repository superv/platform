<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterDeletedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterRetrievedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterSavedHook;

class OrdersObserver implements AfterSavedHook, AfterRetrievedHook, AfterDeletedHook
{
    public static $identifier = 'testing::orders';

    public function saved(EntryContract $entry)
    {
        $entry->setAttribute('title', 'Order Saved');
    }

    public function retrieved(EntryContract $entry)
    {
        $_SERVER['__observer.retrieved'] = [
            'resource' => $entry->getResourceIdentifier(),
        ];
    }

    public function deleted(EntryContract $entry)
    {
        $_SERVER['__observer.deleted'] = [
            'resource' => $entry->getResourceIdentifier(),
            'fresh'    => $entry->fresh(),
        ];
    }
}
