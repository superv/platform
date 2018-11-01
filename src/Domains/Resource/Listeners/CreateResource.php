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

//        if ($event->scope === 'platform') {
//            $dropletId = 0;
//        } else {
//            $dropletEntry = DropletModel::bySlug($event->scope);
//            if (! $dropletEntry) {
//                return;
//            }
//            $dropletId = $dropletEntry->getKey();
//        }

        ResourceModel::create([
            'slug'         => $event->table,
            'model'        => $event->model,
            'droplet_slug' => $event->scope,
        ]);
    }
}