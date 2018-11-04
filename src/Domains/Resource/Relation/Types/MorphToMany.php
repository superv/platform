<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;

class MorphToMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery($instance)
    {
        return $this->newMorphToMany($instance);
    }

    protected function newMorphToMany($instance)
    {
        $parentModel = $this->resource->getEntry();

        return new EloquentMorphToMany(
            $instance->newQuery(),
            $parentModel,
            $this->config->getMorphName(),
            $this->config->getPivotTable(),
            $this->config->getPivotForeignKey(),
            $this->config->getPivotRelatedKey(),
            $parentModel->getKeyName(),
            $instance->getKeyName()
        );
    }
}