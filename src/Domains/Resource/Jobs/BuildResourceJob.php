<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory;
use SuperV\Platform\Domains\Resource\Resource;
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

        $resource->makeEntry();

        $resource->getFields(false)
                 ->transform(function ($field) {
                     $field = (new FieldFactory($this->resource))->make($field);

                     return $field->build();
                 });

        $resource->getRelations()
                 ->transform(function ($relation) {
                     if ($relation instanceof Relation) {
                         return $relation;
                     }

                     return (new RelationFactory($this->resource))->make($relation);
                 });

        $resource->markAsBuilt();
    }
}