<?php

namespace SuperV\Platform\Domains\Resource\Http;

use SuperV\Platform\Domains\Resource\ResourceFactory;

trait ResolvesResource
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $entry;

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    protected function resolveResource()
    {
        if ($this->resource) {
            return $this->resource;
        }
        $resource = request()->route()->parameter('resource');
        $this->resource = ResourceFactory::make(str_replace('-', '_', $resource));

        if (! $this->resource) {
            throw new \Exception("Resource not found [{$resource}]");
        }

        if ($id = request()->route()->parameter('id')) {
            $this->entry = $this->resource->find($id);
        }

        return $this->resource;
    }

    /** @return \SuperV\Platform\Domains\Resource\Relation\Relation */
    protected function resolveRelation()
    {
        $relation = $this->resolveResource()->getRelation($this->route->parameter('relation'));
        if ($this->entry) {
            $relation->acceptParentEntry($this->entry);
        }

        return $relation;
    }
}