<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreateResource
{
    /** @var string */
    protected $table;

    /** @var ResourceConfig */
    protected $blueprint;

    /** @var string */
    protected $addon;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function handle(TableCreatingEvent $event)
    {
        if (! $event->addon) {
            return;
        }

        $this->addon = $event->addon;
        $this->table = $event->table;
        $this->blueprint = $event->resourceBlueprint;

        $this->createResourceEntry($this->blueprint->config($this->table, $event->columns), $event->addon);

        $this->resource = ResourceFactory::make($this->table);

        $this->createNavSections();
    }

    protected function createNavSections()
    {
        if ($nav = $this->blueprint->nav) {
            if (is_string($nav)) {
                Section::createFromString($handle = $nav.'.'.$this->table, null, $this->addon);
                $section = Section::get($handle);
                $section->update([
                    'url'    => 'sv/res/'.$this->table,
                    'title'  =>  $this->table.'.label',
                    'handle' => str_slug($this->blueprint->label, '_'),
                ]);
            } elseif (is_array($nav)) {
                if (! isset($nav['url'])) {
                    $nav['url'] = 'sv/res/'.$this->table;
                }
                $section = Section::createFromArray($nav);
            }

            $section->update(['addon' => $this->addon]);
        }
    }

    protected function createResourceEntry($config, $addon)
    {
        /** @var ResourceModel $entry */
       return ResourceModel::create(array_filter(
            [
                'slug'       => $this->table,
                'handle'     => $this->table,
                'model'      => $this->blueprint->model,
                'config'     => $config,
                'addon'      => $addon,
                'restorable' => (bool)$this->blueprint->restorable,
                'sortable'   => (bool)$this->blueprint->sortable,
            ]
        ));
    }
}