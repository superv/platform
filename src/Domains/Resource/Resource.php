<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource implements ProvidesFields, ProvidesQuery
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
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $freshFields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $relations;

    /**
     * @var Closure
     */
    protected $relationProvider;

    /**
     * @var \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel
     */
    protected $entry;

    protected $entryId;

    protected $titleFieldId;

    protected $model;

    protected $handle;

    protected $label;

    protected $entryLabel;

    /**
     * @var boolean
     */
    protected $built = false;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    public function build()
    {
        return $this;
    }

    public function newEntryInstance()
    {
        if ($model = $this->getConfigValue('model')) {
            return new ResourceEntry(new $model);
        }

        return ResourceEntry::newInstance($this);
    }

    public function create(array $attributes = []): ResourceEntry
    {
//        $entry = ResourceEntryModel::make($this->getHandle())->create($attributes);
        $entry = $this->newEntryInstance()->create($attributes);

        return ResourceEntry::make($entry, $this->fresh());
    }

    public function find($id): ?ResourceEntry
    {
        if (! $entry = $this->newQuery()->find($id)) {
            return null;
        }

        return ResourceEntry::make($entry, $this->fresh());
    }

    public function first(): ?ResourceEntry
    {
        if (! $entry = $this->newQuery()->first()) {
            return null;
        }

        return ResourceEntry::make($entry, $this->fresh());
    }

    public function count(): int
    {
        return $this->newQuery()->count();
    }

    public function fresh(): self
    {
        return static::of($this->getHandle());
    }

    /** @return \SuperV\Platform\Domains\Resource\Model\ResourceEntry|array */
    public function fake(array $overrides = [], int $number = 1)
    {
        return ResourceEntry::fake($this, $overrides, $number);
    }

    public function route($route)
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'index') {
            return $base;
        }
    }

    public function provideFields(): Collection
    {
        return $this->getFields();
    }

    public function getFields(): Collection
    {
        $self = ResourceFactory::make('sv_resources')
                               ->newQuery()
                               ->with('fields')
                               ->where('handle', $this->getHandle())
                               ->first();

        return $self->fields->map(function (ResourceEntryModel $fieldEntry) {
            return FieldFactory::createFromArray($fieldEntry->toArray());
        });

//        if ($this->fields instanceof Closure) {
//            $this->fields = ($this->fields)();
//        }
//
//        return $this->fields;
    }

    public function getFieldType($name): ?FieldType
    {
        $field = $this->getField($name);

        return $field->resolveType();
    }

    public function getField($name): ?Field
    {
        return $this->getFields()->first(function ($field) use ($name) { return $field->getName() === $name; });
    }

    public function getRelations(): Collection
    {
//        $self = ResourceFactory::make('sv_resources')
//                               ->newQuery()
//                               ->with('relations')
//                               ->where('handle', $this->getHandle())
//                               ->first();
//
//        return $self->relations->map(function (ResourceEntryModel $relationEntry) {
//            return RelationFactory::resolveFromRelationEntry($relationEntry);
//        })
//                               ->keyBy(function (Relation $relation) { return $relation->getName(); });
//
        if ($this->relations instanceof Closure) {
            $this->relations = ($this->relations)();
        }

        return $this->relations;
    }

    public function getRelation($name, ?ResourceEntry $entry = null): ?Relation
    {
//        return $this->getRelations()->get($name);
        return ($this->relationProvider)($name, $entry);
    }

    public function getLabel()
    {
        return $this->getConfigValue('label');
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

    public function __sleep()
    {
        if ($this->relations instanceof Closure) {
            $this->relations = null;
        }

        if ($this->fields instanceof Closure) {
            $this->fields = null;
        }

        $this->relationProvider = null;

        return array_diff(array_keys(get_object_vars($this)), []);
    }

    public function __wakeup()
    {
        $this->hydrate(ResourceFactory::attributesFor($this->getHandle()));
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function id(): int
    {
        return $this->id;
    }

    public static function modelOf($handle)
    {
        if (! $resourceEntry = ResourceModel::withSlug($handle)) {
            throw new PlatformException("Resource model not found with handle [{$handle}]");
        }

        if ($model = $resourceEntry->getConfigValue('model')) {
            return new $model;
        }

        return ResourceEntryModel::make($resourceEntry->getSlug());
    }

    public static function of($handle): self
    {
        return ResourceFactory::make($handle);
    }
}