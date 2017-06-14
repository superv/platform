<?php namespace SuperV\Platform\Domains\Entry;

use SuperV\Nucleus\Domains\Entry\Entry;
use SuperV\Platform\Contracts\Dispatcher;

class EntryObserver
{
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function creating(Entry $entry)
    {
        $entry->creating = true;
    }

    public function created(Entry $entry)
    {
    }

    public function updating(Entry $entry)
    {
    }

    public function updated(Entry $entry)
    {
    }

    public function updatedMultiple(Entry $entry)
    {
        $entry->flushCache();
    }

    public function saving($entry)
    {
        return true;
    }

    public function saved(Entry $entry)
    {
        if ($entry->creating) {
            return;
        }
        $class = get_class($entry->nucleo());

        $observer = substr($class, 0, -5) . 'Observer';
        $observing = class_exists($observer);
        if ($observing) {
            (new $observer)->saved($entry);
        }
    }

    public function deleting(Entry $entry)
    {
        return true;
    }

    public function deleted(Entry $entry)
    {
        $entry->flushCache();
    }

    public function deletedMultiple(Entry $entry)
    {
        $entry->flushCache();
    }
}