<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Current;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntryCreatingEvent;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class SaveCreatedBy
{
    public function handle(EntryCreatingEvent $event)
    {
        $entry = $event->entry;

        if (! Resource::exists($entry)) {
            return;
        }

        $resource = ResourceFactory::make($entry);

        if ($resource->fields()->find('created_by')) {
            if ($user = auth()->user()) {
                $entry->created_by_id = $user->id;
            } elseif (Current::isConsole()) {
                $entry->created_by_id = 0;
            }
        }

        if ($resource->fields()->find('created_at')) {
            $entry->setAttribute('created_at', Current::time());
        }
    }
}
