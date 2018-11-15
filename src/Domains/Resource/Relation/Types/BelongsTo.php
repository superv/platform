<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsParentResourceEntry;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class BelongsTo extends Relation implements AcceptsParentResourceEntry
{
    protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation
    {
        return new EloquentBelongsTo(
            $relatedEntryInstance->newQuery(),
            $this->parentResourceEntry->getEntry(),
            $this->config->getForeignKey(),
            'id',
            $this->getName()
        );
    }
}