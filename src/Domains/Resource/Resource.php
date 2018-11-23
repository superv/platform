<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Context\Context;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesColumns;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTableConfig;
use SuperV\Platform\Domains\Resource\Contracts\Providings\ProvidesRoute;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field as FieldContract;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryFake;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\TableColumn;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource implements Arrayable, ProvidesFields, ProvidesQuery, ProvidesRoute, ProvidesColumns, ProvidesTableConfig
{
    use Hydratable;
    use HasConfig;

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

    protected $entryId;

    protected $titleFieldId;

    protected $model;

    protected $handle;

    protected $label;

    protected $entryLabel;

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

    public function getFields(): Collection
    {
        if ($this->fields instanceof Closure) {
            $this->fields = ($this->fields)();
        }

        if (! $this->fields) {
            return collect();
        }

        return $this->fields->keyBy(function (FieldContract $field) { return $field->getName(); });
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

    public function getField($name): ?FieldContract
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

    public function getLabel()
    {
        return $this->getConfigValue('label');
    }

    public function getLabelOfEntry(EntryContract $entry)
    {
        return sv_parse($this->getConfigValue('entry_label'), $entry->toArray());
    }

    public function getSingularLabel()
    {
        return $this->getConfigValue('singular_label', str_singular($this->getConfigValue('label')));
    }

    public function getResourceKey()
    {
        return $this->getConfigValue('resource_key', str_singular($this->getHandle()));
    }

    public function getEntryLabelTemplate()
    {
        return $this->getConfigValue('entry_label');
    }

    public function getSlug(): string
    {
        return $this->getHandle();
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function newQuery()
    {
        return $this->newEntryInstance()->newQuery();
    }

    public function provideRoute(string $name)
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($name === 'create') {
            return $base.'/create';
        }

        if ($name === 'index') {
            return $base;
        }
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
                                           ->map(function (FieldContract $field) {
                                               if ($field->getConfigValue('table.show') === true) {
                                                   return TableColumn::fromField($field);
                                               }

                                               return null;
                                           })
                                           ->filter()
                                  );

        return $this->columns;

        $labelField = FieldFactory::createFromArray(['name' => 'label', 'label' => $this->getSingularLabel()]);
        $labelField->onPresenting(function ($entry) {
            return sv_parse($this->getConfigValue('entry_label'), $entry->toArray());
        });

        $this->columns = collect()->put('label', $labelField)->merge(
            $this->getFields()->filter(
                function (FieldContract $field) {
                    return $field->getConfigValue('table.show') === true;
                }
            )
        );

        return $this->columns;
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
            'uuid' => $this->uuid,
            'handle' => $this->getHandle(),
        ];
    }

    public static function modelOf($handle)
    {
        if (! $resourceEntry = ResourceModel::withHandle($handle)) {
            throw new PlatformException("Resource model not found with handle [{$handle}]");
        }

        if ($model = $resourceEntry->getConfigValue('model')) {
            return new $model;
        }

        return ResourceEntry::make($resourceEntry->getHandle());
    }

    public static function of($handle): self
    {
        if ($handle instanceof EntryContract) {
            $handle = $handle->getTable();
        }

        return ResourceFactory::make($handle);
    }
}