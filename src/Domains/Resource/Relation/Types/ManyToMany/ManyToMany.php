<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types\ManyToMany;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Action\DetachEntryAction;
use SuperV\Platform\Domains\Resource\Action\ViewEntryAction;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Builder\RelationBlueprint;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Relation\Actions\LookupAttachablesAction;
use SuperV\Platform\Domains\Resource\Relation\Contracts\ProvidesField;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ManyToMany extends Relation implements ProvidesTable, ProvidesField
{
    public function makeTable()
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

    /**
     * @param \SuperV\Platform\Domains\Resource\Relation\Types\BelongsToMany\Config $blueprint
     * @param \SuperV\Platform\Domains\Resource\Driver\DriverInterface              $driver
     */
    public function driverCreating(RelationBlueprint $blueprint, DriverInterface $driver)
    {
        if ($driver instanceof DatabaseDriver) {
            $pivot = $blueprint->getPivot();
            if ($pivot->shouldCreate() && ! SchemaService::resolve()->tableExists($pivot->getHandle())) {
                $pivot->id();

                // Owner Field
                $pivot->belongsTo($blueprint->getRelated(), $pivot->getRelatedKey())
                      ->foreignKey($pivot->getRelatedKey().'_id');

                // Related Field
                $pivot->belongsTo($blueprint->getParent()->getIdentifier(), $pivot->getForeignKey())
                      ->foreignKey($pivot->getForeignKey().'_id');

                Builder::resolve()->save($pivot);
            }
        }
    }
}
