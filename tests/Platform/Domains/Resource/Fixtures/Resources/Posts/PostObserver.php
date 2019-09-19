<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Hook\Contracts\BeforeSavingHook;

class PostObserver implements BeforeSavingHook
{
    public static $identifier = 'testing.posts';

    public function saving(EntryContract $entry)
    {
        $entry->setAttribute('title', $entry->title.' Saving');
    }
}
