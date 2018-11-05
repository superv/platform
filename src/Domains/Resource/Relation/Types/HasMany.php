<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;


use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;

class HasMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
    {
        return new EloquentHasMany(
            $instance->newQuery(),
            $this->resource->getEntry(),
            $this->config->getForeignKey(),
            $this->resource->getEntry()->getKeyName()
        );
    }

}