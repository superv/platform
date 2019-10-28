<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\Events;
use SuperV\Platform\Support\Dispatchable;

class RegisterEntryEventListeners
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [
        'creating'  => Events\EntryCreatingEvent::class,
        'created'   => Events\EntryCreatedEvent::class,
        'saving'    => Events\EntrySavingEvent::class,
        'saved'     => Events\EntrySavedEvent::class,
        'deleted'   => Events\EntryDeletedEvent::class,
        'retrieved' => Events\EntryRetrievedEvent::class,
    ];

    public function handle(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        foreach ($this->map as $eventName => $eventClass) {
            $this->dispatcher->listen(
                sprintf("eloquent.%s:*", $eventName),
                function ($event, $payload) use ($eventClass, $eventName) {
                    if (($entry = $payload[0]) instanceof EntryContract) {
                        $this->dispatcher->dispatch(sprintf("%s.entry.events:%s", $entry->getResourceIdentifier(), $eventName), $entry);
                        $eventClass::dispatch($entry);
                    }
                }
            );
        }
    }
}
