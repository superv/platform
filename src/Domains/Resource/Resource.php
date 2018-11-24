<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Context\Context;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryFake;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\TableColumn;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource implements
    Contracts\ProvidesFields,
    Contracts\ProvidesQuery,
    Contracts\ProvidesColumns,
    Contracts\ProvidesTableConfig
{
    use Hydratable;
    use HasConfig;
    use LabelConcern;

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
     * @var Collection
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

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry
     */
    protected $entry;

    /** @var string */
    protected $handle;

    /** @var \SuperV\Platform\Domains\Resource\Table\TableConfig */
    protected $tableConfig;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    public function newEntryInstance()
    {
        if ($model = $this->getConfigValue('model')) {
            // Custom Entry Model
            $entry = new $model;
        } else {
            // Anonymous Entry Model
            $entry = ResourceEntry::make($this->getHandle());
        }

        return $entry;
    }

    public function create(array $attributes = []): EntryContract
    {
        return $this->newEntryInstance()->create($attributes);
    }

    public function find($id): ?EntryContract
    {
        if (! $entry = $this->newQuery()->find($id)) {
            return null;
        }

        return $entry;
    }

    public function first(): ?EntryContract
    {
        if (! $entry = $this->newQuery()->first()) {
            return null;
        }

        return $entry;
    }

    public function count(): int
    {
        return $this->newQuery()->count();
    }

    /** @return \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|array */
    public function fake(array $overrides = [], int $number = 1)
    {
        return ResourceEntryFake::make($this, $overrides, $number);
    }

    public function getFields(): Collection
    {
        if ($this->fields instanceof Closure) {
            $this->fields = ($this->fields)();
        }

        if (! $this->fields) {
            return collect();
        }

        return $this->fields->keyBy(function (Field $field) { return $field->getName(); });
    }

    public function provideFields(): Collection
    {
        return $this->getFields();
    }

    public function getFieldType($name): ?FieldType
    {
        $field = $this->getField($name);

        return $field->fieldType();
    }

    public function getField($name): ?Field
    {
        return $this->getFields()->first(function ($field) use ($name) { return $field->getName() === $name; });
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

//        $tokens = [
//            'res'      => $this->toArray(),
//            'entry'    => $entry ? $entry->toArray() : ['id' => 'NULL'],
//            'required' => $entry && $entry->exists ? 'sometimes|required' : 'required',
//        ];
//
//        return sv_parse($rules, $tokens);
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
        }

        return $rules;
    }

    public function getResourceKey()
    {
        return $this->getConfigValue('resource_key', str_singular($this->getHandle()));
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function newQuery()
    {
        return $this->newEntryInstance()->newQuery();
    }

    public function provideColumns(): Collection
    {
        if ($this->columns) {
            return $this->columns;
        }

        $labelColumn = TableColumn::make('label')
                                  ->setLabel($this->getSingularLabel())
                                  ->setTemplate($this->getConfigValue('entry_label'));

        $this->columns = collect()->put('label', $labelColumn)
                                  ->merge(
                                      $this->getFields()
                                           ->map(function (Field $field) {
                                               if ($field->getConfigValue('table.show') === true) {
                                                   return TableColumn::fromField($field);
                                               }

                                               return null;
                                           })
                                           ->filter()
                                  );

        return $this->columns;
    }

    public function route($route, ?EntryContract $entry = null)
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'index' || $route === 'store') {
            return $base;
        }

        if ($route === 'update') {
            return $base.'/'.$entry->getId().'/update';
        }
    }

    public function provideTableConfig(): TableConfig
    {
        if ($this->tableConfig) {
            return $this->tableConfig;
        }

        return $this->tableConfig = TableConfig::make()
                                               ->setDataUrl(url()->current().'/data')
                                               ->setColumns($this->provideColumns())
                                               ->setQuery($this)
                                               ->setContext(new Context($this))
                                               ->build();
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

    public static function of($handle): self
    {
        if ($handle instanceof EntryContract) {
            $handle = $handle->getTable();
        }

        return ResourceFactory::make($handle);
    }
}