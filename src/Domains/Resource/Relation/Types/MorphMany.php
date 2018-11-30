<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphMany as EloquentMorphMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\TableV2;

class MorphMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentMorphMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getMorphName().'_type',
            $this->relationConfig->getMorphName().'_id',
            'id'
        );
    }

    public function makeTable()
    {
        return app(TableV2::class)
            ->setResource($this->getRelatedResource())
            ->setQuery($this)
            ->setDataUrl(url()->current().'/data')
            ->mergeFields($this->getPivotFields());
    }
}