<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreateResource
{
    /** @var string */
    protected $table;

    /** @var ResourceBlueprint */
    protected $blueprint;

    public function handle(TableCreatingEvent $event)
    {
        if (! $event->scope) {
            return;
        }

        $this->table = $event->table;
        $this->blueprint = $event->resourceBlueprint;

        $this->createResourceEntry($this->blueprint->config($this->table, $event->columns), $event->scope);

        $this->createNavSections();
    }

    protected function createNavSections()
    {
        if ($nav = $this->blueprint->nav) {
            if (is_string($nav)) {
                Section::createFromString($handle = $nav.'.'.$this->table);
                $section = Section::get($handle);
                $section->update([
                    'url'    => 'sv/api/res/'.$this->table,
                    'title'  => $this->blueprint->label,
                    'handle' => str_slug($this->blueprint->label, '_'),
                ]);
            } elseif (is_array($nav)) {
                if (! isset($nav['url'])) {
                    $nav['url'] = 'sv/api/res/'.$this->table;
                }
                Section::createFromArray($nav);
            }
        }
    }

    protected function createResourceEntry($config, $addon)
    {
        /** @var ResourceModel $entry */
        ResourceModel::create(array_filter(
            [
                'slug'       => $this->table,
                'handle'     => $this->table,
                'model'      => $this->blueprint->model,
                'config'     => $config,
                'addon'      => $addon,
                'restorable' => (bool)$this->blueprint->restorable,
            ]
        ));
    }
}