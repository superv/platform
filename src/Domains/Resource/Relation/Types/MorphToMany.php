<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphToMany as EloquentMorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\AttachEntryAction;
use SuperV\Platform\Domains\Resource\Action\DetachEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\TableV2;

class MorphToMany extends Relation implements ProvidesTable
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentMorphToMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getMorphName(),
            $this->relationConfig->getPivotTable(),
            $this->relationConfig->getPivotForeignKey(),
            $this->relationConfig->getPivotRelatedKey(),
            $this->parentEntry->getKeyName(),
            $relatedEntryInstance->getKeyName()
        );
    }

    public function makeTable()
    {
        return app(TableV2::class)
            ->setResource($this->getRelatedResource())
            ->setQuery($this)
            ->addAction(DetachEntryAction::make()->setRelation($this))
            ->setDataUrl(url()->current().'/data')
            ->addContextAction(AttachEntryAction::make()->setRelation($this))
            ->mergeFields($this->getPivotFields());
    }
}