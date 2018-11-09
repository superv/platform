<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\Model\EntryModel;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
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

    protected $slug;

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
            return new $model;
        }

        return Entry::newInstance($this);
    }

    public function create(array $attributes = []): EntryModel
    {
        return $this->newEntryInstance()->create($attributes);
    }

    public function createAndLoad(array $attributes = [])
    {
        $this->entry = $this->create($attributes);

        return $this;
    }

    public function createFake(array $overrides = [], int $number = 1)
    {
        if ($number > 1) {
            return collect(range(1, $number))->map(function () use ($overrides) {
                return $this->createFake($overrides, 1);
            })->all();
        }

        return Fake::create($this, $overrides);
    }

    public function freshWithFake(array $overrides = []): self
    {
        return $this->fresh()->setEntry($this->createFake($overrides));
    }

    public function loadEntry($entryId): self
    {
        $this->entry = $this->newEntryInstance()->newQuery()->find($entryId);

        return $this;
    }

    public function saveEntry(array $params = [])
    {
        $entry = $this->getEntry();
        EntrySavingEvent::dispatch($entry, $params);
        $entry->save();
    }

    public function getEntry(): ?ResourceEntryModel
    {
        return $this->entry;
    }

    public function setEntry(ResourceEntryModel $entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function getEntryId()
    {
        return $this->entry ? $this->entry->getId() : null;
    }

    public function getFields(): Collection
    {
        if ($this->fields instanceof Closure) {
            $this->fields = ($this->fields)($this->getEntry());
        }

        return $this->fields;
    }

    public function getFieldEntry($name): ?FieldModel
    {
        return optional($this->getFieldType($name))->getEntry();
    }

    public function getFieldType($name): ?FieldType
    {
        $field = $this->getField($name);

        $fieldType = FieldType::fromEntry(FieldModel::withUuid($field->uuid()));
        if (! $this->getEntry() || $fieldType->getEntry()) {
            return $fieldType;
        }

        return $fieldType->setEntry(Entry::make($this->getEntry()));
    }

    public function getField($name): ?Field
    {
        return $this->fields->first(function (Field $field) use ($name) { return $field->getName() === $name; });
    }

    public function getRelations(): Collection
    {
        if ($this->relations instanceof Closure) {
            $this->relations = ($this->relations)($this->getEntry());
        }

        return $this->relations;
    }

    public function getRelation($name, Entry $entry): ?Relation
    {
        return ($this->relationProvider)($name, $entry);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function fresh($build = false): self
    {
        return static::of($this->getHandle(), $build);
    }

    public function getLabel()
    {
        return $this->getConfigValue('label');
    }

    public function singularLabel()
    {
        return $this->getConfigValue('singular_label', str_singular($this->getConfigValue('label')));
    }

    public function entryLabelTemplate()
    {
        return $this->getConfigValue('entry_label');
    }

    public function entryLabel()
    {
        $label = $this->getConfigValue('entry_label');

        return sv_parse($label, $this->getEntry()->toArray());
//        return $this->singularLabel().' #'.$this->getEntryId();
    }

    public function getSlug(): string
    {
        return $this->getHandle();
    }

    public function getHandle(): string
    {
        return $this->slug;
    }

    public function markAsBuilt()
    {
        $this->built = true;
    }

    public function newQuery()
    {
        return $this->newEntryInstance()->newQuery()->select($this->getHandle().'.*');
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
        PlatformException::fail("Resource is offfff");

        /** @var \SuperV\Platform\Domains\Resource\Resource $resource */
        if ($handle instanceof ResourceEntryModel) {
            $resource = ResourceFactory::make($handle->getTable());
            $resource->setEntry($handle);
        } else {
            $resource = ResourceFactory::make($handle);
        }

        return $resource;
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
        if ($this->entryId) {
            $this->loadEntry($this->entryId);
        } else {
            $this->entry = $this->newEntryInstance();
        }
    }
}