<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Context\Context;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource\LabelConcern;
use SuperV\Platform\Domains\Resource\Resource\RepoConcern;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
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
    protected $entryss;

    /** @var string */
    protected $handle;

    /** @var \SuperV\Platform\Domains\Resource\Table\TableConfig */
    protected $tableConfig;

    /** @var Closure */
    protected $viewResolver;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
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

    public function getTableFields(): Collection
    {
        $labelField = FieldFactory::createFromArray([
            'type'      => 'text',
            'name'      => 'label',
            'label'     => $this->getSingularLabel(),
            'presenter' => function (EntryContract $entry) {
                return sv_parse($this->getConfigValue('entry_label'), $entry->toArray());
            },

        ]);

     return collect()->put('label', $labelField)
                                  ->merge(
                                      $this->getFields()
                                           ->map(function (Field $field) {
                                               if ($field->getConfigValue('table.show') === true) {
                                                   return $field;
                                               }

                                               return null;
                                           })
                                           ->filter()
                                  );

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

    public function getHandle(): string
    {
        return $this->handle;
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
}