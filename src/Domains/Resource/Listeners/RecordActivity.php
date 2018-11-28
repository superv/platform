<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Current;
use SuperV\Platform\Domains\Resource\Resource\ResourceActivityEvent;

class RecordActivity
{
    public function handle(ResourceActivityEvent $event)
    {
        $route = $event->request->route();
        if (! $this->shouldRecord($activity = $route->getName())) {
            return;
        }

        $entryType = sv_resource($route->parameter('resource'))->newEntryInstance()->getMorphClass();
        sv_resource('sv_activities')->create([
            'user_id'    => $event->request->user()->getKey(),
            'entry_type' => $entryType,
            'entry_id'   => $route->parameter('id'),
            'activity'   => $activity,
            'created_at' => Current::time(),
        ]);
    }

    protected function shouldRecord($activity)
    {
        return in_array($activity, ['resource.view']);
    }
}