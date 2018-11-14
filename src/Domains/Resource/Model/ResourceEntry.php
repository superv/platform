<?php

namespace SuperV\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Morphable;
use SuperV\Platform\Domains\Resource\Fake;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;

class ResourceEntry implements Watcher
{
    /** @var Resource */
    protected $resource;

    /**
     * @var ResourceEntryModel
     */
    protected $entry;

    protected $handle;

    protected $entryId;

    protected $config;

    public function __construct($entry, ?Resource $resource = null)
    {
        $this->entry = $entry;
        $this->handle = $this->entry->getTable();
        $this->resource = $resource;
    }

    public function id()
    {
        return $this->getEntry()->getKey();
    }

    public function getEntry(): EntryContract
    {
        return $this->entry;
    }

    public function exists()
    {
        return $this->getEntry() && $this->getEntry()->exists;
    }

    public function __sleep()
    {
        if ($this->entry) {
            if ($this->entry->exists) {
                $this->entryId = $this->entry->id;
            }
        }

        return array_keys(array_except(get_object_vars($this), ['entry']));
    }

    public function __wakeup()
    {
        if (! $this->handle) {
            return;
        }

//        $resource = Resource::of($this->getHandle());

        if (! $this->entryId) {
            $this->entry = static::newInstance($this->getHandle())->getEntry();

            return;
        }

        $this->entry = Resource::of($this->getHandle())->find($this->entryId)->getEntry();
    }

    public function setAttribute($key, $value)
    {
        $this->getEntry()->setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        return $this->getEntry()->getAttribute($key);
    }

    public function save()
    {
        return $this->getEntry()->save();
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getResource(): Resource
    {
        if (! $this->resource) {
            $this->resource = Resource::of($this->getHandle());
        }

        return $this->resource;
    }

    public function getLabel()
    {
        $label = $this->getResource()->getConfigValue('entry_label');

        return sv_parse($label, $this->getEntry()->toArray());
    }

    public function route($route)
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($route === 'edit') {
            return $base.'/'.$this->id().'/edit';
        }
        if ($route === 'delete') {
            return $base.'/'.$this->id().'/delete';
        }
    }

    public function getField(string $name): ?Field
    {
        $field = $this->getResource()->getField($name);
        if ($field instanceof FieldModel) {
            $field = FieldFactory::createFromEntry($field);
        }

        return $field->setWatcher($this);
    }

    public function getFieldType(string $name): ?FieldType
    {
        $fieldType = FieldType::fromEntry(FieldModel::withUuid($this->getField($name)->uuid()));
        $fieldType->setEntry($this);

        return $fieldType;
    }

    public function newQuery()
    {
        return $this->entry->newQuery();
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }
        if ($relation = $this->entry->getRelationshipFromConfig($name)) {
            return $relation;
        }

        return call_user_func_array([$this->entry, $name], $arguments);
    }

    public function __get($key)
    {
        return $this->entry->{$key};
    }

    public static function make($entry, ?Resource $resource = null): self
    {
        return new static($entry, $resource);
    }

    public static function fake($resource, array $overrides = [], int $number = 1)
    {
        if (is_string($resource)) {
            $resource = ResourceFactory::make($resource);
        }

        if ($resource instanceof Resource) {
            if ($number > 1) {
                return collect(range(1, $number))
                    ->map(function () use ($resource, $overrides) {
                        return static::fake($resource, $overrides, 1);
                    })
                    ->all();
            }

            return Fake::create($resource, $overrides);
        }

        PlatformException::fail("Can not fake, resource not found");
    }

    public static function newInstance($handle): ResourceEntry
    {
        if (is_string($handle)) {
            return new static(ResourceEntryModel::make($handle));
        }

        if (($resource = $handle) instanceof Resource) {
            return new static(ResourceEntryModel::make($resource->getHandle()), $resource);
        }
    }

    public function getOwnerType()
    {
        return $this->getHandle();
    }

    public function getOwnerId()
    {
        return $this->id();
    }
}