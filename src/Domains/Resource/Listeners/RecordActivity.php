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

        $resource = sv_resource($route->parameter('resource'));
        $entryType = $resource->newEntryInstance()->getMorphClass();

        sv_resource('platform::sv_activities')->create([
            'user_id'     => $event->request->user()->getKey(),
            'resource_id' => $resource->id(),
            'entry_type'  => $entryType,
            'entry_id'    => (int)$route->parameter('id'),
            'activity'    => $activity,
            'payload'     => json_encode($event->request->all()),
            'created_at'  => Current::time(),
        ]);
    }

    protected function shouldRecord($activity)
    {
        return starts_with($activity, ['resource.', 'relation'])
            && ! in_array($activity, ['resource.dashboard']);
    }
}
