<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class BelongsTo extends Relation
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
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