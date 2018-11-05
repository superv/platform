<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Field\Builder;
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

        $fields = $this->getFields();
        $this->resource->setFields($fields);
        $this->resource->setRelations($this->entry->getResourceRelations());

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

    public function getFields()
    {
        if ($extension = Resource::extension($this->handle)) {
            $extension = app($extension);

            if (method_exists($extension, 'fields')) {
                $fields = sv_collect($extension->fields())->map(function ($field) {
                    return (new Builder($this->resource))->build($field);
                });
            }
        }

        if ($this->entry->getFields()->count() > 30) {
            throw new PlatformException('Somethings wrong here: '.$this->entry->getFields()->count());
        }

        return $fields ?? $this->entry->getFields();
    }

    public static function make(string $slug)
    {
        return (new static($slug))->build();
    }
}