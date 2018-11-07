<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Resource\Field\Builder as FieldBuilder;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Relation\Builder as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Dispatchable;

class BuildResourceJob
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function handle()
    {
        $resource = $this->resource;
        if ($resource->isBuilt()) {
            throw new PlatformException('Resource is already built.');
        }

        $resource->makeEntry();

        $resource->getFields(false)
                 ->transform(function ($field) {
//                     if ($field instanceof Field) {
//                         return $field;
//                     }

                     return (new FieldBuilder($this->resource))->build($field);
                 });

        $resource->getRelations()
                 ->transform(function ($relation) {
                     if ($relation instanceof Relation) {
                         return $relation;
                     }

                     return (new RelationBuilder($this->resource))->build($relation);
                 });

        $resource->markAsBuilt();
    }
}