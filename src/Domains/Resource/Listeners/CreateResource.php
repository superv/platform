<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Database\ResourceRepository;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Jobs\CreateNavigation;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Exceptions\ValidationException;

class CreateResource
{
    /** @var ResourceConfig */
    protected $config;

    /** @var \SuperV\Platform\Domains\Database\Events\TableCreatingEvent */
    protected $event;

    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $resourceEntry;

    /**
     * @var \SuperV\Platform\Domains\Resource\Database\ResourceRepository
     */
    protected $repository;

    public function __construct(ResourceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(TableCreatingEvent $event)
    {
        if (! $event->namespace) {
            return;
        }

        $this->event = $event;

        $this->config = $event->config;

        $this->processConfig();

        try {
            $this->resourceEntry = $this->repository->create($this->config);
        } catch (ValidationException $e) {
            dd($e->all());
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
        if ($nav = $this->config->getNav()) {
            $section = CreateNavigation::resolve($this->config)
                                       ->create($nav, $this->resourceEntry->getId());

            $section->update(['namespace' => $this->event->namespace]);
        }
    }

    protected function processConfig(): void
    {
        if (! $this->config->getEntryLabel()) {
            $this->guessEntryLabel($this->config, $this->event->columns);
        }

        if ($this->config->isRestorable()) {
            $this->event->blueprint->nullableBelongsTo('platform.users', 'deleted_by')->hideOnForms();
            $this->event->blueprint->timestamp('deleted_at')->nullable()->hideOnForms();
        }

        if ($this->config->isSortable()) {
            $this->event->blueprint->unsignedBigInteger('sort_order')->default(0);;
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
