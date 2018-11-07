<?php

namespace SuperV\Platform\Domains\Resource;

use Exception;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Jobs\BuildResourceJob;
use SuperV\Platform\Domains\Resource\Model\EntryModel;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory;
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
    protected $freshFields;

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
        log_callers();

        if ($this->isBuilt()) {
            throw new PlatformException('Resource is already built.');
        }

        $this->makeEntry();

        // keep unbuilt fields
        $this->freshFields = collect();

        $this->getFields(false)
                 ->transform(function ($field) {
                     $field = (new FieldFactory($this))->make($field);

                     $this->freshFields->push($field->copy());

                     return $field->build();
                 });

        $this->getRelations()
                 ->transform(function ($relation) {
                     if ($relation instanceof Relation) {
                         return $relation;
                     }

                     return (new RelationFactory($this))->make($relation);
                 });

        $this->markAsBuilt();

        return $this;
    }

    public function copyFreshFields(): Collection
    {
        return $this->freshFields->map(function(Field $field) {
            return $field->copy();
        });
    }

    public function newEntryInstance()
    {
        if ($model = $this->getConfigValue('model')) {
            return new $model;
        }

        return ResourceEntryModel::make($this->handle());
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

    public function getFields($ensureBuilt = true): Collection
    {
        if ($ensureBuilt) {
            $this->ensureBuilt();
        }

        return $this->fields;
    }

    public function setFields(Collection $fields): self
    {
        $this->ensureNotBuilt();

        $fields->map(function ($field) {
            if ($field instanceof Field && $field->isBuilt()) {
                throw new Exception("Can not accept a built field");
            }
        });

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

    public function ensureNotBuilt()
    {
        if ($this->isBuilt()) {
            throw new Exception('Resource is already built');
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
        $base = 'sv/res/'.$this->handle();
        if ($route === 'edit') {
            return $base.'/'.$this->getEntryId().'/edit';
        }
        if ($route === 'delete') {
            return $base.'/'.$this->getEntryId().'/delete';
        }
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'index') {
            return $base;
        }

        if ($route === 'table') {
            return 'sv/tables/'.$params['uuid'];
        }
    }

    public function fresh($build = false): self
    {
        return static::of($this->handle(), $build);
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
            $this->entry = $this->newEntryInstance();
        }
    }

    public function makeEntry(): void
    {
        if (! $this->entry) {
            $this->entry = $this->newEntryInstance();
        }
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

    public function slug(): string
    {
        return $this->handle();
    }

    public function handle(): string
    {
        return $this->slug;
    }

    public function markAsBuilt()
    {
        $this->built = true;
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

    public static function of($handle, bool $build = true): self
    {
        /** @var \SuperV\Platform\Domains\Resource\Resource $resource */
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