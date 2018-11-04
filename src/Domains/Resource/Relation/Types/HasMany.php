<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;


use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;

class HasMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery($instance)
    {
        return $this->newHasMany($instance);
    }

    protected function newHasMany($instance)
    {
        return new EloquentHasMany(
            $instance->newQuery(),
            $this->resource->getEntry(),
            $this->config->getForeignKey(),
            $this->resource->getEntry()->getKeyName()
        );
    }
}