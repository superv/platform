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

        /** @var ResourceModel $entry */
        $entry = ResourceModel::create(array_filter(
            [
                'slug'         => $event->table,
                'model'        => $resource->model,
                'config'       => $resource->config($event->table, $event->columns),
                'droplet' => $event->scope,
            ]
        ));

        if ($nav = $resource->nav) {
            if (is_string($nav)) {
                $parts = explode('.', $nav);

                $nav = [
                    'nav'        => $parts[0],
                    'section'    => $parts[1] ?? null,
                    'subsection' => $parts[2] ?? null,
                ];
            }
            if (! isset($nav['title'])) {
                $nav['title'] = $entry->getConfigValue('label');
            }
            if (! isset($nav['slug'])) {
                $nav['slug'] = str_slug($nav['title'], '_');
            }
            $entry->nav()->create(array_filter($nav));
        }
    }
}