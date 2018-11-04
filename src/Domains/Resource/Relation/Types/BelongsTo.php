<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;

class BelongsTo extends Relation
{
    protected function newRelationQuery($instance)
    {
        return $this->newBelongsTo($instance);
    }

    protected function newBelongsTo($instance)
    {
        return new EloquentBelongsTo(
            $instance->newQuery(),
            $this->resource->getEntry(),
            $this->config->getForeignKey(),
            'id',
            $this->getName()
        );
    }
}