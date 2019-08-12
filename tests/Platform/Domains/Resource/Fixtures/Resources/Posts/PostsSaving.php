<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Hook\BeforeSavingHook;
use SuperV\Platform\Domains\Resource\Resource;

class PostsSaving implements BeforeSavingHook
{
    public function before(EntryContract $entry, Resource $resource)
    {
        $entry->setAttribute('title', $entry->title.' Before');
    }
}