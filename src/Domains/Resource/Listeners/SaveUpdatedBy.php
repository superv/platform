<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Current;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class SaveUpdatedBy
{
    public function handle(EntrySavingEvent $event)
    {
        $entry = $event->entry;

        if (! Resource::exists($entry)) {
            return;
        }

        $resource = ResourceFactory::make($entry);

        if ($resource->fields()->find('updated_by')) {
            if ($user = auth()->user()) {
                $entry->updated_by_id = $user->id;
            } elseif (Current::isConsole()) {
                $entry->updated_by_id = 0;
            }
        }

        if ($resource->fields()->find('updated_at')) {
            $entry->setAttribute('updated_at', Current::time());
        }
    }
}