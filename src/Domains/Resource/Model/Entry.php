<?php

namespace SuperV\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Resource\Fake;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;

class Entry implements Watcher
{
    /** @var Resource */
    protected $resource;

    /**
     * @var ResourceEntryModel
     */
    protected $entry;

    protected $handle;

    protected $entryId;

    public function __construct(ResourceEntryModel $entry, ?Resource $resource = null)
    {
        $this->entry = $entry;
        $this->handle = $this->entry->getTable();
        $this->resource = $resource;
    }

    public function id()
    {
        return $this->entry->getKey();
    }

    public function getEntry(): ResourceEntryModel
    {
        return $this->entry;
    }

    public function exists()
    {
        return $this->entry && $this->entry->exists;
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

        $resource = Resource::of($this->handle);

        if (! $this->entryId) {
            $this->entry = $resource->newEntryInstance();

            return;
        }

        $this->entry = $resource->loadEntry($this->entryId)->getEntry();
    }

    public function setAttribute($key, $value)
    {
        $this->entry->setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        return $this->entry->getAttribute($key);
    }

    public function save()
    {
        return $this->entry->save();
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getResource(): Resource
    {
        return $this->resource;
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
        if ($route === 'create') {
            return $base.'/create';
        }

        if ($route === 'index') {
            return $base;
        }
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

            return static::make(Fake::create($resource), $resource);
        }

        PlatformException::fail("Can not fake, resource not found");
    }

    public static function newInstance($handle): Entry
    {
        if (is_string($handle)) {
            return new static(ResourceEntryModel::make($handle));
        }

        if (($resource = $handle) instanceof Resource) {
            return new static(ResourceEntryModel::make($resource->getHandle()), $resource);
        }
    }
}