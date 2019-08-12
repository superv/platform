<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Resource\Hook;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavedEvent;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class HookSaved
{
    public function handle(EntrySavedEvent $event)
    {
        $entry = $event->entry;

        if (! Resource::exists($entry) || starts_with($entry->getTable(), 'sv_')) {
            return;
        }

        $resource = ResourceFactory::make($entry);

        Hook::saved($entry, $resource);
    }
}