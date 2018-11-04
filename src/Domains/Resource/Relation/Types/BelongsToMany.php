<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;

class BelongsToMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery($instance)
    {
        return $this->newBelongsToMany($instance);
    }

    protected function newBelongsToMany($instance)
    {
        return new EloquentBelongsToMany(
            $instance->newQuery(),
            $this->resource->getEntry(),
            $this->config->getPivotTable(),
            $this->config->getPivotForeignKey(),
            $this->config->getPivotRelatedKey(),
            $this->resource->getEntry()->getKeyName(),
            $instance->getKeyName()
        );
    }
}