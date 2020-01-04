<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Current;
use Exception;
use SuperV\Platform\Domains\Resource\Resource\ResourceActivityEvent;

class RecordActivity
{
    public function handle(ResourceActivityEvent $event)
    {
        $route = $event->request->route();
        if (! $this->shouldRecord($activity = $route->getName())) {
            return;
        }

        try {
            $resource = sv_resource($route->parameter('resource'));
            $entryType = $resource->newEntryInstance()->getMorphClass();
            sv_resource('sv.platform.activities')->create([
                'user_id'     => $event->request->user()->getKey(),
                'resource_id' => $resource->id(),
                'entry_type'  => $entryType,
                'entry_id'    => (int)$route->parameter('entry'),
                'activity'    => $activity,
                'payload'     => json_encode($event->request->all()),
                'created_at'  => Current::time(),
            ]);
        } catch (Exception $e) {
            sv_console("Can not record activity: ".$e->getMessage());
        }
    }

    protected function shouldRecord($activity)
    {
        return starts_with($activity, ['resource.', 'relation', 'sv::entry'])
            && ! in_array($activity, ['resource.dashboard']);
    }
}
