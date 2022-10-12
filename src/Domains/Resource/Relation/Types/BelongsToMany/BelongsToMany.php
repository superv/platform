<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\DetachEntryAction;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Relation\Actions\LookupAttachablesAction;
use SuperV\Platform\Domains\Resource\Relation\Contracts\ProvidesField;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Table\Table;

class BelongsToMany extends Relation implements ProvidesTable, ProvidesField
{
    public function makeTabl___v2()
    {
        $relatedResource = ResourceFactory::make($this->getRelationConfig()->getPivotIdentifier());

        $relatedResource->config()->entryLabelField($this->getRelatedResource()->config()->getResourceKey());

        $detachAction = DetachEntryAction::make($relatedResource->getChildIdentifier('actions', 'detach'))
                                         ->setRelation($this);
        $attachAction = LookupAttachablesAction::make($relatedResource->getChildIdentifier('actions', 'attach'))
                                               ->setRelation($this);
        $viewAction = ViewEntryAction::make($relatedResource->getChildIdentifier('actions', 'view'));

        $query = $relatedResource->newQuery();
        if ($entry = $this->getParentEntry()) {
            $query->where($this->getRelationConfig()->getPivotForeignKey(), $entry->getId());
        }

        return $relatedResource->resolveTable()
                               ->setQuery($query)
                               ->addRowAction($viewAction)
                               ->addRowAction($detachAction)
                               ->addContextAction($attachAction)
                               ->setDataUrl(url()->current().'/data');
    }

    public function makeTable()
    {
        $relatedResource = $this->getRelatedResource();
        $detachAction = DetachEntryAction::make($relatedResource->getChildIdentifier('actions', 'detach'))
                                         ->setRelation($this);
        $attachAction = LookupAttachablesAction::make($relatedResource->getChildIdentifier('actions', 'attach'))
                                               ->setRelation($this);
        $viewAction = ViewEntryAction::make($relatedResource->getChildIdentifier('actions', 'view'));

        return Table::resolve()
                    ->setResource($relatedResource)
                    ->setQuery($this->newQuery())
                    ->addRowAction($viewAction)
                    ->addRowAction($detachAction)
                    ->addContextAction($attachAction)
                    ->setDataUrl(url()->current().'/data')
                    ->mergeFields($this->getPivotFields());
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
        return new EloquentBelongsToMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getPivotTable(),
            $this->relationConfig->getPivotForeignKey(),
            $this->relationConfig->getPivotRelatedKey(),
            $this->parentEntry->getKeyName(),
            $relatedEntryInstance->getKeyName()
        );
    }
}
