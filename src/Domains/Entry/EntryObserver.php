<?php

namespace SuperV\Platform\Domains\Entry;

use SuperV\Platform\Contracts\Dispatcher;

class EntryObserver
{
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function creating(EntryModel $entry)
    {
    }

    public function created(EntryModel $entry)
    {
        if ($callback = $entry->getOnCreateCallback()) {
            return call_user_func($callback, $entry);
        }
    }

    public function updating(EntryModel $entry)
    {
    }

    public function updated(EntryModel $entry)
    {
    }

    public function saving(EntryModel $entry)
    {
        return true;
    }

    public function saved(EntryModel $entry) { }

    public function deleting(EntryModel $entry)
    {
        return true;
    }

    public function deleted(EntryModel $entry)
    {
        $entry->flushCache();
    }

    public function deletedMultiple(EntryModel $entry)
    {
        $entry->flushCache();
    }
}