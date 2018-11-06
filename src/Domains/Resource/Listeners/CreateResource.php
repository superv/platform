<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
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
                'slug'    => $event->table,
                'model'   => $resource->model,
                'config'  => $resource->config($event->table, $event->columns),
                'droplet' => $event->scope,
            ]
        ));

        if ($nav = $resource->nav) {
            if (is_string($nav)) {
                Section::createFromString($handle = $nav.'.'.$event->table);
                $section = Section::get($handle);
                $section->update([
                    'url' => 'sv/res/' . $event->table ,
                    'title' => $resource->label,
                    'handle' => str_slug($resource->label, '_')
                ]);

            } elseif (is_array($nav)) {
                if (!isset($nav['url'])) {
                    $nav['url'] = 'sv/res/' . $event->table;
                }
                Section::createFromArray($nav);
            }
        }
    }
}