<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Contracts\RequiresResource;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntryCreatedEvent;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntryDeletedEvent;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Filter\SearchFilter;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource\Extender;
use SuperV\Platform\Domains\Resource\Resource\Fields;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Resource\LabelConcern;
use SuperV\Platform\Domains\Resource\Resource\RepoConcern;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
use SuperV\Platform\Domains\Resource\Resource\TestHelper;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource implements
    Contracts\ProvidesFields,
    Contracts\ProvidesQuery
{
    use Hydratable;
    use LabelConcern;
    use RepoConcern;
    use FiresCallbacks;

    /**
     * Database id
     *
     * @var int
     */
    protected $id;

    /** @var string */
    protected $identifier;

    protected $name;

    /**
     * Database connection string
     *
     * @var string
     */
    protected $dsn;

    /**
     * Database uuid
     *
     * @var string
     */
    protected $uuid;

    /**
     * Resource namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource\Fields
     */
    protected $fields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fieldEntries;

    /**
     * @var Collection
     */
    protected $relations;

    protected $mergeRelations;

    /**
     * Registered resource actions
     *
     * @var \Illuminate\Support\Collection
     */
    protected $actions;

    protected $searchable = [];

    protected $filters = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource\IndexFields
     */
    protected $indexFields;

    protected $restorable = false;

    protected $sortable = false;

    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    protected $config;

    protected $extended = false;

    /** @var \SuperV\Platform\Domains\Resource\Database\Entry\EntryRepositoryInterface */
    protected $entryRepository;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        $this->fields = (new Fields($this, $this->fields));

        $this->relations = ($this->relations)($this);

        $this->actions = collect();

        $this->entryRepository = EntryRepository::for($this);
    }

    public function config(): ResourceConfig
    {
        return $this->config;
    }

    public function registerAction($identifier, $handler): Resource
    {
        $this->actions->put($identifier, $handler);

        return $this;
    }

    public function getAction($name)
    {
        $action = $this->actions->get($name);
        if (is_string($action)) {
            $action = $action::make($this->getChildIdentifier('actions', $name));
        }

        if ($action instanceof RequiresResource) {
            $action->setResource($this);
        }

        return $action;
    }

    public function onCreated(Closure $callback): Resource
    {
        app('events')->listen(EntryCreatedEvent::class, function (EntryCreatedEvent $event) use ($callback) {
            if ($event->entry->getTable() === $this->config()->getTable()) {
                $callback($event->entry);
            }
        });

        return $this;
    }

    public function onDeleted(Closure $callback): Resource
    {
        app('events')->listen(EntryDeletedEvent::class, function (EntryDeletedEvent $event) use ($callback) {
            if ($event->entry->getTable() === $this->config()->getTable()) {
                $callback($event->entry);
            }
        });

        return $this;
    }

    public function fields(): Fields
    {
        return $this->fields;
    }

    public function indexFields(): IndexFields
    {
        if ($this->indexFields) {
            return $this->indexFields;
        }

        return $this->indexFields = new IndexFields($this);
    }

    public function getFields(): Collection
    {
        return $this->fields()->getAll();
    }

    public function getField($name): ?FieldInterface
    {
        return $this->fields()->get($name);
    }

    public function getFieldEntries(): Collection
    {
        if (is_callable($this->fieldEntries)) {
            $this->fieldEntries = ($this->fieldEntries)();
        }

        return $this->fieldEntries;
    }

    public function getRelations(): Collection
    {
        return $this->relations->merge(collect($this->mergeRelations));
    }

    public function addRelation(Relation $relation)
    {
        $this->cacheRelation($relation);
        $this->mergeRelations[$relation->getName()] = $relation;
    }

    public function getRelation($name, ?EntryContract $entry = null): ?Relation
    {
        $key = $this->getIdentifier().'.'.$name;
        if ($relation = superv('relations')->get($key)) {
            return $relation;
        }
        $relation = $this->getRelations()->get($name);
        if ($entry && $relation instanceof AcceptsParentEntry) {
            $relation->acceptParentEntry($entry);
        }

        if (! $relation) {
            PlatformException::fail("Relation not found: [{$name}]");
        }

        $this->cacheRelation($relation);

        return $relation;
    }

    public function cacheRelation(Relation $relation)
    {
        $key = $this->getIdentifier().'.'.$relation->getName();
        superv('relations')->put($key, $relation);
    }

    public function isOwned()
    {
        return ! is_null($this->config()->getOwnerKey());
    }

    public function getKeyName()
    {
        return $this->config()->getKeyName();
    }

    public function router(): Router
    {
        return new Router($this);
    }

    public function spaRoute($route, ?EntryContract $entry = null, array $params = [])
    {
        if (starts_with($route, 'forms.')) {
            $form = explode('.', $route)[1];

            $formUuid = $form === 'create' || $form === 'edit' ? $this->getIdentifier() : null; // null ??

            $params['uuid'] = $formUuid;
        }

        $parameters = array_merge($params, ['id'       => $entry ? $entry->getId() : null,
                                            'resource' => $this->getIdentifier()]);

        return route('resource.'.$route, array_filter($parameters), false);
    }

    public function route($route, ?EntryContract $entry = null, array $params = [])
    {
        $base = 'sv/res/'.$this->getIdentifier();

        if ($route === 'fields') {
            $params = array_merge($params, ['resource' => $this->getIdentifier()]);

            return sv_route('resource.fields', $params);
        }

        if ($route === 'actions') {
            return $base.'/'.$entry->getId().'/actions';
        }

        if ($route === 'update' || $route === 'delete') {
            return $base.'/'.$entry->getId();
        }

        if (starts_with($route, 'forms.')) {
            $form = explode('.', $route)[1];

            $formUuid = in_array($form, ['create',
                                         'update',
                                         'store',
                                         'edit']) ? $this->getIdentifier() : null; // null ??
            $params['uuid'] = $formUuid;

            $params['entry'] = $entry ? $entry->getId() : null;

            return sv_route('resource.'.$route, $params);
        }

//        if ($route === 'dashboard') {
//            return sv_route('resource.dashboard', ['resource' => $this->getHandle()]);
////            return $base.'/table';
//        }

        $parameters = array_merge($params, ['id'       => $entry ? $entry->getId() : null,
                                            'resource' => $this->getIdentifier()]);

        return sv_route('resource.'.$route, array_filter($parameters));
    }

    public function getIdentifier(): string
    {
        return $this->config()->getIdentifier();
    }

    public function getChildIdentifier($type, $key)
    {
        return sprintf("%s.%s:%s", $this->getIdentifier(), $type, $key);
    }

    public function searchable(array $searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function addFilter($filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function getFilters()
    {
        $filters = wrap_collect($this->filters);
        if (! $searchables = $this->searchable) {
            foreach ($this->fields()->withFlag('searchable') as $field) {
                $searchables[] = $field->getName();
            }
        }
        if ($searchables) {
            $filters->push((new SearchFilter)->setFields($searchables));
        }

        $this->getRelations()
             ->filter(function (Relation $relation) {
                 return $relation->hasFlag('filter') && $relation instanceof ProvidesFilter;
             })->map(function (ProvidesFilter $relation) use ($filters) {
                $filters->push($relation->makeFilter());
            });

        $this->fields()
             ->getFilters()
             ->map(function (Filter $filter) use ($filters) {
                 $filters->push($filter->setResource($this));
             });

        return $filters;
    }

    public function resolveTable(): TableInterface
    {
        $table = Table::resolve()->setResource($this);

        $table->setIdentifier($this->getIdentifier().'.lists:default');

        $this->fire('table.resolved', ['table' => $table]);

        $this->fireEvent('lists:default.events:resolved', ['table' => $table, 'fields' => $this->indexFields()]);

        return $table;
    }

    public function afterTableResolved(Closure $callback)
    {
        $this->on('table.resolved', $callback);

        return $this;
    }

    public function resolveView(EntryContract $entry): ResourceView
    {
        $view = new ResourceView($this, $entry);

        $this->fire('view.resolved', ['view' => $view]);

        return $view;
    }

    public function afterViewResolved(Closure $callback)
    {
        $this->on('view.resolved', $callback);

        return $this;
    }

    public function testHelper()
    {
        return new TestHelper($this);
    }

    public function isRestorable(): bool
    {
        return $this->restorable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function isExtended(): bool
    {
        return $this->extended;
    }

    public function setExtended(bool $extended): void
    {
        $this->extended = $extended;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function toArray()
    {
        return [
            'uuid'           => $this->uuid,
            'identifier'     => $this->getIdentifier(),
            'singular_label' => $this->getSingularLabel(),
        ];
    }

    public static function extend($identifier)
    {
        Extension::register($extender = new Extender($identifier));

        return $extender;
    }

    public static function exists($identifier): bool
    {
        if ($identifier instanceof EntryContract) {
            $identifier = $identifier->getResourceIdentifier();
        }

        if (! $identifier) {
            return false;
        }

        return ResourceModel::query()->where('identifier', $identifier)->exists();
    }

    protected function fireEvent($event, $payload = [])
    {
        $eventName = $this->getIdentifier().'.'.$event;
        app('events')->dispatch($eventName, $payload);
    }

    public function provideFields(): Collection
    {
        return $this->getFields();
    }
}
