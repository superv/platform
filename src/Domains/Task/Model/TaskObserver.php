<?php namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Entry\EntryObserver;

class TaskObserver extends EntryObserver
{
    public function updated(EntryModel $entry)
    {
        parent::updated($entry);

        \Log::info('TASK UPDATED' . $entry->response);
    }
}