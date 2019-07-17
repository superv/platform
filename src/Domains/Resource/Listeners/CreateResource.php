<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreateResource
{
    /** @var string */
    protected $table;

    /** @var ResourceConfig */
    protected $config;

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
        $this->config = $event->resourceConfig;

        if (! $this->config->getEntryLabel()) {
            $this->guessEntryLabel($this->config, $event->columns);
        }

        $this->createResourceEntry($this->config, $event->addon);

//        $this->resource = ResourceFactory::make($this->table);

        $this->createNavSections();
    }

    protected function guessEntryLabel(ResourceConfig $config, Collection $columns): void
    {
        if ($columns->has('name')) {
            $config->entryLabel('{name}');
        } elseif ($columns->has('title')) {
            $config->entryLabel('{title}');
        } elseif ($firstStringColumn = $columns->firstWhere('type', 'string')) {
            $config->entryLabel('{'.$firstStringColumn->name.'}');
        } else {
            $config->entryLabel(str_singular($config->getLabel()).' #{'.$config->getKeyName().'}');
        }
    }

    protected function createNavSections()
    {
        if ($nav = $this->config->getNav()) {
            if (is_string($nav)) {
//                Section::createFromString($handle = $nav.'.'.$this->table, null, $this->addon);
                Section::createFromString($handle = $nav.'.'.$this->table);
                $section = Section::get($handle);
                $section->update([
                    'url'    => 'sv/res/'.$this->table,
                    'title'  => $this->config->getLabel(),
                    'handle' => str_slug($this->config->getLabel(), '_'),
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

    protected function createResourceEntry(ResourceConfig $config, $addon)
    {
        /** @var ResourceModel $entry */
        return ResourceModel::create(array_filter(
            [
                'slug'       => $this->table,
                'handle'     => $this->table,
                'model'      => $this->config->getModel(),
                'config'     => $config->toArray(),
                'addon'      => $addon,
                'restorable' => (bool)$this->config->isRestorable(),
                'sortable'   => (bool)$this->config->isSortable(),
            ]
        ));
    }
}
