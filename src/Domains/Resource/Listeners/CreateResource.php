<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Exceptions\ValidationException;

class CreateResource
{
    /** @var ResourceConfig */
    protected $config;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Events\TableCreatingEvent */
    protected $event;

    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    public function handle(TableCreatingEvent $event)
    {
        if (! $event->namespace) {
            return;
        }

        $this->event = $event;

        $this->config = $event->config;

        $this->processConfig();

        try {
            $this->createResourceEntry();
        } catch (ValidationException $e) {
//            dd($e->all());
        }

        $this->createNavSections();

        ResourceCreatedEvent::fire($this->resourceEntry);
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
//        $table = $this->event->table;
        $identifier = $this->config->getIdentifier();

        if ($nav = $this->config->getNav()) {
            if (is_string($nav)) {
                Section::createFromString($handle = $nav.'.'.$identifier);
                $section = Section::get($handle);
                $section->update([
                    'resource_id' => $this->resourceEntry->getId(),
                    'url'         => 'sv/res/'.$identifier,
                    'title'       => $this->config->getLabel(),
                    'handle'      => str_slug($this->config->getLabel(), '_'),
                ]);
            } elseif (is_array($nav)) {
                if (! isset($nav['url'])) {
                    $nav['url'] = 'sv/res/'.$identifier;
                }
                $section = Section::createFromArray($nav);
            }

            $section->update(['namespace' => $this->event->namespace]);
        }
    }

    protected function createResourceEntry()
    {
        /** @var ResourceModel $entry */
        $this->resourceEntry = ResourceModel::create(array_filter(
            [
                'uuid'       => uuid(),
                'identifier' => $this->config->getIdentifier(),
                'full_id'    => $this->event->namespace.'.'.$this->config->getIdentifier(),
                'dsn'        => $this->config->getDriver()->toDsn(),
                'model'      => $this->config->getModel(),
                'config'     => $this->getConfig(),
                'namespace'  => $this->event->namespace,
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

        if (! $this->config->getIdentifier()) {
            $this->config->setIdentifier($this->event->table);
        }
    }

    /**
     * @return array
     */
    protected function getConfig(): array
    {
        return $this->config->toArray();
    }
}
