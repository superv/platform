<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\Table\RelationTableConfig;
use SuperV\Platform\Domains\Resource\Table\TableConfig;

class MorphToMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation
    {
        return new EloquentMorphToMany(
            $relatedEntryInstance->newQuery(),
            $this->parentResourceEntry->getEntry(),
            $this->config->getMorphName(),
            $this->config->getPivotTable(),
            $this->config->getPivotForeignKey(),
            $this->config->getPivotRelatedKey(),
            $this->parentResourceEntry->getEntry()->getKeyName(),
            $relatedEntryInstance->getKeyName()
        );
    }

    public function makeTableConfig(): TableConfig
    {
        return (new RelationTableConfig($this))->build();
    }
}