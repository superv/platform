<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;

class MorphToMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery(ResourceEntryModel $instance): EloquentRelation
    {
        return new EloquentMorphToMany(
            $instance->newQuery(),
            $this->getParentEntry(),
            $this->config->getMorphName(),
            $this->config->getPivotTable(),
            $this->config->getPivotForeignKey(),
            $this->config->getPivotRelatedKey(),
            $this->getParentEntry()->getKeyName(),
            $instance->getKeyName()
        );
    }
}