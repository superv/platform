<?php

namespace SuperV\Platform\Domains\Resource;

use Exception;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Exceptions\PlatformException;

class ResourceFactory
{
    /**
     * @var string
     */
    protected $handle;

    /** @var \SuperV\Platform\Domains\Resource\ResourceModel */
    protected $entry;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    public function build()
    {
        $this->loadEntry();

        $this->makeResource();

        $this->resource->setFields($this->getFields()->map(function ($field) {
            if (is_object($field)) {
                $field = clone $field;
            };

            return $field;
        }));

        $this->resource->setRelations($this->getRelations());

        return $this->resource;
    }

    /**
     * @return null|\SuperV\Platform\Domains\Resource\ResourceModel
     * @throws \SuperV\Platform\Exceptions\PlatformException
     */
    public function loadEntry()
    {
        if (! $this->entry = ResourceModel::withSlug($this->handle)) {
            throw new PlatformException("Resource model entry not found for [{$this->handle}]");
        }

        return $this->entry;
    }

    public function makeResource(): void
    {
        $this->resource = new Resource();
        $entryArray = array_except($this->entry->toArray(), ['fields', 'relations']);

        $this->resource->hydrate($entryArray);
    }

    public function getFields(): Collection
    {
        if ($extension = Extension::get($this->handle)) {
            if (method_exists($extension, 'fields')) {
                $fields = collect($extension->fields());
            }
        }

        if ($this->entry->getFields()->count() > 30) {
            throw new Exception('Somethings wrong here: '.$this->entry->getFields()->count());
        }

        return $fields ?? $this->entry->getFields();
    }

    protected function getRelations()
    {
        return $this->entry->getResourceRelations()->map(function ($relation) {
            if (is_object($relation)) {
                $relation = clone $relation;
            };

            return $relation;
        });
    }

    public static function make(string $slug): Resource
    {
        return (new static($slug))->build();
    }
}