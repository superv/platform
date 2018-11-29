<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Filter\SearchFilter;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource\Extender;
use SuperV\Platform\Domains\Resource\Resource\Fields;
use SuperV\Platform\Domains\Resource\Resource\LabelConcern;
use SuperV\Platform\Domains\Resource\Resource\RepoConcern;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource implements
    Contracts\ProvidesFields,
    Contracts\ProvidesQuery
{
    use Hydratable;
    use HasConfig;
    use LabelConcern;
    use RepoConcern;

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

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        $this->fields = (new Fields($this, $this->fields));
    }

    public function resolveViewUsing(Closure $closure)
    {
        $this->viewResolver = $closure;

        return $this;
    }

    public function resolveView(?EntryContract $entry = null): ResourceView
    {
        if ($this->viewResolver) {
            return ($this->viewResolver)($entry);
        }

        return new ResourceView($this, $entry);
    }

    public function fields(): Fields
    {
        return $this->fields;
    }

    public function getFields(): Collection
    {
        return $this->fields()();
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
        if ($this->relations instanceof Closure) {
            $this->relations = ($this->relations)();
        }

        return $this->relations;
    }

    public function getRelation($name, ?EntryContract $entry = null): ?Relation
    {
        $relation = $this->getRelations()->get($name);
        if ($entry && $relation instanceof AcceptsParentEntry) {
            $relation->acceptParentEntry($entry);
        }

        return $relation;
    }

    public function getRules(EntryContract $entry = null)
    {
        return $this->getFields()
                    ->keyBy(function (Field $field) {
                        return $field->getColumnName();
                    })
                    ->map(function (Field $field) use ($entry) {
                        return $this->parseFieldRules($field, $entry);
                    })
                    ->filter()
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

    public function route($route, ?EntryContract $entry = null)
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'index.table') {
            return $base.'/table';
        }

        if ($route === 'index' || $route === 'store') {
            return $base;
        }

        if ($route === 'view' || $route === 'edit') {
            return $base.'/'.$entry->getId().'/'.$route;
        }
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
        if (! $this->searchable) {
            foreach ($this->fields()->withFlag('searchable') as $field) {
                $this->searchable[] = $field->getName();
            }
        }

        return wrap_collect($this->filters)
            ->when(! empty($this->searchable),
                function ($filters) {
                    return $filters->push((new SearchFilter)->setFields($this->searchable));
                }
            );
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
            'uuid'   => $this->uuid,
            'handle' => $this->getHandle(),
        ];
    }

    public static function extend($handle)
    {
        Extension::register($extender = new Extender($handle));

        return $extender;
    }
}