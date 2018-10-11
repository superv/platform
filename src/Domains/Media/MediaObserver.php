<?php

namespace SuperV\Platform\Domains\Media;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Entry\EntryObserver;

class MediaObserver extends EntryObserver
{
    public function deleted(EntryModel $entry)
    {
        parent::deleted($entry);

        \Storage::delete($entry->getBasename());
    }
}