<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class MorphTo extends Relation
{
    protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation
    {
        $ownerKey = $this->config->getName().'_id';
        $type = $this->config->getName().'_type';

        return new EloquentMorphTo(
            $relatedEntryInstance->newQuery(),
            $this->parentResourceEntry->getEntry(),
            $this->config->getForeignKey(),
            $ownerKey,
            $type,
            $this->getName()
        );
    }
}