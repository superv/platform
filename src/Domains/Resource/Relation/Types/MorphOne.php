<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphOne as EloquentMorphOne;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphOne extends Relation
{
    protected function newRelationQuery($instance)
    {
        $parentModel = $this->resource->getEntry();
        $morphName = $this->config->getMorphName();

        return new EloquentMorphOne(
            $instance->newQuery(),
            $parentModel,
            $morphName.'_type',
            $morphName.'_id',
            $parentModel->getKeyName()
        );
    }
}