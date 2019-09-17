<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Hook\Contracts\AfterCreatedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\BeforeCreatingHook;

class CategoryObserver implements BeforeCreatingHook, AfterCreatedHook
{
    public static $identifier = 'testing.categories';

    public function creating(EntryContract $entry)
    {
        $_SERVER['__hooks::observer.creating'] = [
            'resource' => $entry->getResourceIdentifier(),
            'exists'   => $entry->exists(),
        ];
    }

    public function created(EntryContract $entry)
    {
        $_SERVER['__hooks::observer.created'] = [
            'resource' => $entry->getResourceIdentifier(),
            'exists'   => $entry->exists(),
        ];
    }
}
