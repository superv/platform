<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class BelongsToMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
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