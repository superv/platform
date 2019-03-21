<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Contracts\RequiresResource;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Filter\SearchFilter;
use SuperV\Platform\Domains\Resource\Model\Events\EntryCreatedEvent;
use SuperV\Platform\Domains\Resource\Model\Events\EntryDeletedEvent;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource\Extender;
use SuperV\Platform\Domains\Resource\Resource\Fields;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Resource\LabelConcern;
use SuperV\Platform\Domains\Resource\Resource\RepoConcern;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
use SuperV\Platform\Domains\Resource\Resource\TestHelper;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

final class Resource implements
    Contracts\ProvidesFields,
    Contracts\ProvidesQuery
{
    use Hydratable;
    use HasConfig;
    use LabelConcern;
    use RepoConcern;
    use FiresCallbacks;

    /**
     * Database id
     *
     * @var int
     */
    protected $id;

    /**
     * Database uuid
     *
     * @var string
     */
    protected $uuid;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource\Fields
     */
    protected $fields;

    /**
     * @var Collection
     */
    protected $columns;

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

    /**
     * @var Closure
     */
    protected $relationProvider;

    /** @var string */
    protected $handle;

    /** @var Closure */
    protected $viewResolver;

    protected $searchable = [];

    protected $filters = [];

    /** @var \SuperV\Platform\Domains\Resource\Resource\IndexFields */
    protected $indexFields;

    protected $onCreatedCallbacks = [];

    protected $restorable = false;

    protected $sortable = false;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        $this->fields = (new Fields($this, $this->fields));
        $this->relations = ($this->relations)($this);
        $this->actions = collect();
    }

    public function registerAction($identifier, $handler): Resource
    {
        $this->actions->put($identifier, $handler);

        return $this;
    }

    public function getAction($identifier)
    {
        $action = $this->actions->get($identifier);
        if (is_string($action)) {
            $action = $action::make();
        }

        if ($action instanceof RequiresResource) {
            $action->setResource($this);
        }

        return $action;
    }

    public function onCreated(Closure $callback): Resource
    {
        app('events')->listen(EntryCreatedEvent::class, function (EntryCreatedEvent $event) use ($callback) {
            if ($event->entry->getTable() === $this->getHandle()) {
                $callback($event->entry);
            }
        });

        return $this;
    }

    public function onDeleted(Closure $callback): Resource
    {
        app('events')->listen(EntryDeletedEvent::class, function (EntryDeletedEvent $event) use ($callback) {
            if ($event->entry->getTable() === $this->getHandle()) {
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

    public function provideFields(): Collection
    {
        return $this->getFields();
    }

    public function getField($name): ?Field
    {
        return $this->fields()->get($name);
    }

    public function getRelations(): Collection
    {
//        if ($this->relations instanceof Closure) {
//            $this->relations = ($this->relations)($this);
//        }

        return $this->relations->merge(collect($this->mergeRelations));
    }

    public function addRelation(Relation $relation)
    {
        $this->cacheRelation($relation);
        $this->mergeRelations[$relation->getName()] = $relation;
    }

    public function getRelation($name, ?EntryContract $entry = null): ?Relation
    {
        $key = $this->getHandle().'.'.$name;
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
        $key = $this->getHandle().'.'.$relation->getName();
        superv('relations')->put($key, $relation);
    }

    public function getRules(EntryContract $entry = null)
    {
        $rules = $this->getFields()
                      ->filter(function (Field $field) {
                          return ! $field->isUnbound();
                      })
                      ->keyBy(function (Field $field) {
                          return $field->getColumnName();
                      })
                      ->map(function (Field $field) use ($entry) {
                          $rules = $this->parseFieldRules($field, $entry);

                          return $rules;
                      });

        return $rules->filter()
                     ->all();
    }

    public function parseFieldRules($field, ?EntryContract $entry = null)
    {
        $field = is_string($field) ? $this->getField($field) : $field;

        $rules = $field->getRules();

        if ($field->isUnique()) {
            $rules[] = sprintf(
                'unique:%s,%s,%s,id',
                $this->getHandle(),
                $field->getColumnName(),
                $entry ? $entry->getId() : 'NULL'
            );
        }
        if ($field->isRequired()) {
            if ($entry && $entry->exists) {
                $rules[] = 'sometimes';
            }
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        return $rules;
    }

    public function getResourceKey()
    {
        return $this->getConfigValue('resource_key', str_singular($this->getHandle()));
    }

    public function route($route, ?EntryContract $entry = null, array $params = [])
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'index.table') {
            return $base.'/table';
        }

        if ($route === 'index') {
            return $base;
        }

        if ($route === 'store') {
            return $base;
        }

        if ($route === 'view') {
            return $base.'/'.$entry->getId().'/view';
        }

        if ($route === 'view.page') {
            return $base.'/'.$entry->getId();
        }

        if ($route === 'edit') {
            return $base.'/'.$entry->getId().'/'.$route;
        }

        if ($route === 'update') {
            return $base.'/'.$entry->getId();
        }
        if ($route === 'delete' || $route === 'edit' || $route === 'view') {
            return $base.'/'.$entry->getId().'/'.$route;
        }

        return sv_route('resource.'.$route, array_merge($params, ['id'       => $entry->getId(),
                                                                  'resource' => $this->getHandle()]));
    }

    public function getHandle(): string
    {
        return $this->handle;
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

        $this->getRelations()->filter(function (Relation $relation) {
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

    public function resolveTable(): ResourceTable
    {
        $table = app(ResourceTable::class)
            ->setResource($this)
            ->addRowAction(ViewEntryAction::class);

        $this->fire('table.resolved', ['table' => $table]);

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

    public function hasUuid(): bool
    {
        return $this->getConfigValue('has_uuid', false);
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function id(): int
    {
        return $this->id;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'uuid'           => $this->uuid,
            'handle'         => $this->getHandle(),
            'singular_label' => $this->getSingularLabel(),
        ];
    }

    public static function extend($handle)
    {
        Extension::register($extender = new Extender($handle));

        return $extender;
    }

    public static function exists($handle): bool
    {
        if ($handle instanceof EntryContract) {
            $handle = $handle->getTable();
        }

        return ResourceModel::query()->where('handle', $handle)->exists();
    }
}