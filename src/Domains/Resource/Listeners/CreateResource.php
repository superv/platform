<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreateResource
{
    /** @var ResourceConfig */
    protected $config;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Events\TableCreatingEvent */
    protected $event;

    public function handle(TableCreatingEvent $event)
    {
        if (! $event->addon) {
            return;
        }

        $this->event = $event;

        $this->config = $event->config;

        $this->processConfig();

        $this->createResourceEntry();

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
        $table = $this->event->table;

        if ($nav = $this->config->getNav()) {
            if (is_string($nav)) {
//                Section::createFromString($handle = $nav.'.'.$this->table, null, $this->addon);
                Section::createFromString($handle = $nav.'.'.$table);
                $section = Section::get($handle);
                $section->update([
                    'url'    => 'sv/res/'.$table,
                    'title'  => $this->config->getLabel(),
                    'handle' => str_slug($this->config->getLabel(), '_'),
                ]);
            } elseif (is_array($nav)) {
                if (! isset($nav['url'])) {
                    $nav['url'] = 'sv/res/'.$table;
                }
                $section = Section::createFromArray($nav);
            }

            $section->update(['addon' => $this->event->addon]);
        }
    }

    protected function createResourceEntry()
    {
        /** @var ResourceModel $entry */
        return ResourceModel::create(array_filter(
            [
                'slug'       => $this->event->table,
                'handle'     => $this->event->table,
                'model'      => $this->config->getModel(),
                'config'     => $this->config->toArray(),
                'addon'      => $this->event->addon,
                'restorable' => (bool)$this->config->isRestorable(),
                'sortable'   => (bool)$this->config->isSortable(),
            ]
        ));
    }

    protected function processConfig(): void
    {
        if (! $this->config->getEntryLabel()) {
            $this->guessEntryLabel($this->config, $this->event->columns);
        }

        if ($this->config->isRestorable()) {
            $this->event->blueprint->nullableBelongsTo('users', 'deleted_by')->hideOnForms();
            $this->event->blueprint->timestamp('deleted_at')->nullable()->hideOnForms();
        }

        if ($this->config->isSortable()) {
            $this->event->blueprint->unsignedBigInteger('sort_order')->default(0);;
        }
    }
}
