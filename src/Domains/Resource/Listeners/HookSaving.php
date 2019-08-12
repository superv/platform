<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Resource\Hook;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class HookSaving
{
    public function handle(EntrySavingEvent $event)
    {
        $entry = $event->entry;

        if (! Resource::exists($entry)) {
            return;
        }

        $resource = ResourceFactory::make($entry);

        Hook::saving($entry, $resource);
    }
}