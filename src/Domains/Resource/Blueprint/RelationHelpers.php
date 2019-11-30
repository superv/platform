<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Relation\RelationType;
use SuperV\Platform\Domains\Resource\Relation\Types\BelongsTo\BelongsToBlueprint;

/**
 * Trait RelationHelpers
 *
 * @mixin \SuperV\Platform\Domains\Resource\Blueprint\Blueprint
 * @package SuperV\Platform\Domains\Resource\Blueprint
 */
trait RelationHelpers
{
    public function belongsTo(string $relatedResource, string $relationName): BelongsToBlueprint
    {
        return $this->addRelation($relatedResource, $relationName, RelationType::belongsTo());
    }
}