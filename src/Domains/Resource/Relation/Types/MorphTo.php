<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphTo extends Relation
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        $ownerKey = $this->config->getName().'_id';
        $type = $this->config->getName().'_type';

        return new EloquentMorphTo(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->config->getForeignKey(),
            $ownerKey,
            $type,
            $this->getName()
        );
    }
}