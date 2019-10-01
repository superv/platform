<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;

class CreateResourceAuthActions
{
    public function handle(ResourceCreatedEvent $event)
    {
        $resourceEntry = $event->resourceEntry;

        if ($resourceEntry->getNamespace() !== 'platform') {
            Action::query()->create([
                'namespace' => $resourceEntry->getNamespace(),
                'slug'      => $resourceEntry->getNamespace().'.'.$resourceEntry->getName(),
            ]);

//            $resourceEntry->getFields()->map(function(FieldModel $field) use ($resourceEntry) {
//                Action::query()->create([
//                    'namespace' => $resourceEntry->getNamespace(),
//                    'slug'      => $resourceEntry->getNamespace().'.'.$resourceEntry->getName(),
//                ]);
//            });
        }
    }
}