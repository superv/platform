<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasOne as EloquentHasOne;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class HasOne extends Relation
{
    protected function newRelationQuery($instance)
    {
        $parentModel = $this->resource->getEntry();

        return new EloquentHasOne(
            $instance->newQuery(),
            $parentModel,
            $this->config->getForeignKey(),
            $this->config->getLocalKey()
        );
    }
}