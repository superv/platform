<?php

namespace SuperV\Platform\Domains\Resource;

use Exception;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Builder as FieldBuilder;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Jobs\BuildResourceJob;
use SuperV\Platform\Domains\Resource\Model\EntryModel;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

class Resource
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
    protected $relations;

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

    public function build()
    {
        BuildResourceJob::dispatch($this);

        return $this;
    }

    public function resolveModel()
    {
        if ($model = $this->getConfigValue('model')) {
            return new $model;
        }

        return ResourceEntryModel::make($this->slug());
    }

    public function create(array $attributes = []): EntryModel
    {
        return $this->resolveModel()->create($attributes);
    }

    public function createAndLoad(array $attributes = [])
    {
        $this->entry = $this->create($attributes);

        return $this;
    }

    /**
     * @param array $overrides
     * @param int   $number
     * @return ResourceEntryModel|array[ResourceEntryModel]
     */
    public function createFake(array $overrides = [], int $number = 1)
    {
        if ($number > 1) {
            return collect(range(1, $number))->map(function () use ($overrides) {
                return $this->createFake($overrides, 1);
            })->all();
        }

        return Fake::create($this, $overrides);
    }

    public function loadFake(array $overrides = []): self
    {
        $this->entry = $this->createFake($overrides);

        return $this;
    }

    public function loadEntry($entryId): self
    {
        $this->entry = $this->resolveModel()->newQuery()->find($entryId);

        return $this;
    }

    public function saveEntry()
    {
        $this->getEntry()->save();
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
        return $this->fields;
    }

    public function setFields(Collection $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getFieldEntry($name): ?FieldModel
    {
        return optional($this->getField($name))->getEntry();
    }

    public function getField($name): ?Field
    {
        $this->ensureBuilt();

        return $this->fields->first(function (Field $field) use ($name) { return $field->getName() === $name; });
    }

    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function setRelations(Collection $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    public function getRelation($name): ?Relation
    {
        $this->ensureBuilt();

        return $this->relations->first(function (Relation $relation) use ($name) {
            return $relation->getName() === $name;
        });
    }

    public function ensureBuilt()
    {
        if (! $this->isBuilt()) {
            throw new Exception('Resource is not built yet');
        }
    }

    public function isBuilt(): bool
    {
        return $this->built;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function route($route, array $params = [])
    {
        $base = 'sv/resources/'.$this->slug();
        if ($route === 'edit') {
            return $base.'/'.$this->getEntryId().'/edit';
        }
        if ($route === 'delete') {
            return $base.'/'.$this->getEntryId().'/delete';
        }
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'table') {
            return $base.'/table';
        }

        if ($route === 'table.data') {
            return 'sv/tables/'.$params['uuid'];
        }
    }

    public function fresh($build = false): self
    {
        return static::of($this->slug(), $build);
    }

    public function __sleep()
    {
        if ($this->entry && $this->entry->exists) {
            $this->entryId = $this->entry->getKey();
        }

        return array_diff(array_keys(get_object_vars($this)), ['entry']);
    }

    public function __wakeup()
    {
        if ($this->entryId) {
            $this->loadEntry($this->entryId);
        } else {
            $this->entry = $this->resolveModel();
        }
    }

    public function makeEntry(): void
    {
        if (! $this->entry) {
            $this->entry = $this->resolveModel();
        }
    }

    public function buildFields(): void
    {
        $this->fields = $this->fields
            ->map(function ($field) {
                if ($field instanceof Field) {
                    return $field;
                }

                return (new FieldBuilder($this))->build($field);
            });
    }

    public function label()
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

    public function slug()
    {
        return $this->slug;
    }

    public function markAsBuilt()
    {
        $this->built = true;
    }

    public static function modelOf($handle)
    {
        if (!$resourceEntry = ResourceModel::withSlug($handle)) {
          throw new PlatformException("Resource model not found with handle [{$handle}]");
        }

        if ($model = $resourceEntry->getConfigValue('model')) {
            return new $model;
        }

        return ResourceEntryModel::make($resourceEntry->getSlug());
    }

    public static function of($handle, bool $build = true): self
    {
        if ($handle instanceof ResourceEntryModel) {
            $resource = ResourceFactory::make($handle->getTable());
            $resource->setEntry($handle);
        } else {
            $resource = ResourceFactory::make($handle);
        }

        if ($build) {
            return $resource->build();
        }

        return $resource;
    }

}