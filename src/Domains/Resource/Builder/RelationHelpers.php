<?php

namespace SuperV\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Relation\RelationType;
use SuperV\Platform\Domains\Resource\Relation\Types\BelongsTo\Config as BelongsToConfig;
use SuperV\Platform\Domains\Resource\Relation\Types\BelongsToMany\Config as BelongsToManyConfig;
use SuperV\Platform\Domains\Resource\Relation\Types\HasMany\HasManyBlueprint;

/**
 * Trait RelationHelpers
 *
 * @mixin \SuperV\Platform\Domains\Resource\Builder\Blueprint
 * @package SuperV\Platform\Domains\Resource\Builder
 */
trait RelationHelpers
{
    public function morphOne(string $relatedResource, string $relationName): RelationBlueprint
    {
        return $this->addRelation($relatedResource, $relationName, RelationType::morphOne());
    }

    public function hasMany(string $relatedResource, string $relationName): HasManyBlueprint
    {
        return $this->addRelation($relatedResource, $relationName, RelationType::hasMany());
    }

    public function belongsToMany(string $relatedResource, string $relationName): BelongsToManyConfig
    {
        return $this->addRelation($relatedResource, $relationName, RelationType::belongsToMany());
    }

    public function belongsTo(string $relatedResource, string $relationName): BelongsToConfig
    {
        return $this->addRelation($relatedResource, $relationName, RelationType::belongsTo());
    }
}