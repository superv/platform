<?php

namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Entry\EntryObserver;
use SuperV\Platform\Domains\Task\Event\TaskCreatedEvent;

class TaskObserver extends EntryObserver
{
    public function created(EntryModel $entry)
    {
        parent::created($entry);

        $this->events->dispatch(new TaskCreatedEvent($entry, $entry->getPresenter()->statusLabel()));
    }

    public function updated(EntryModel $entry)
    {
        parent::updated($entry);
    }
}
