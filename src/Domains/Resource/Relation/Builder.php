<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use SuperV\Platform\Domains\Resource\Contracts\HasResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;

class Builder
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    protected $base = 'SuperV\Platform\Domains\Resource\Relation\Types';

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function build($relation)
    {
        if ($relation instanceof RelationModel) {
            $relation = static::resolveFromRelationEntry($relation);
        }

        if ($relation instanceof HasResource) {
            $relation->setResource($this->resource);
        }

        return $relation;
    }

    public static function resolveFromRelationEntry(RelationModel $entry): Relation
    {
        /** @var \SuperV\Platform\Domains\Resource\Relation\Relation $class */
        $class = Relation::resolveClass($entry->getType());
        if (! class_exists($class)) {
            throw new PlatformException("Relation class not found for type ".$entry->getType());
        }

        return $class::fromEntry($entry);
    }
}