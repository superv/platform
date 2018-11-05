<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreateResource
{
    public function handle(TableCreatingEvent $event)
    {
        if (! $event->scope) {
            return;
        }

        $resource = $event->blueprint;

        ResourceModel::create(array_filter(
            [
                'slug'         => $event->table,
                'config'       => $resource->config($event->table, $event->columns),
                'droplet_slug' => $event->scope,
            ]
        ));
    }
}