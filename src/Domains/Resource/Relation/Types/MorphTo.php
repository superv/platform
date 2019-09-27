<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Exceptions\PlatformException;

class MorphTo extends Relation
{
    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
//        $ownerKey = $this->relationConfig->getName().'_id';
//        $type = $this->relationConfig->getName().'_type';

        [$type, $ownerKey] = $this->getMorphs($this->relationConfig->getName());

        return new EloquentMorphTo(
            $relatedEntryInstance ? $relatedEntryInstance->newQuery() : ResourceEntry::query()->setEagerLoads([]),
            $this->parentEntry,
            $this->relationConfig->getForeignKey() ?? $ownerKey,
            null,
            $type,
            $this->getName()
        );
    }

    public function newQuery(): EloquentRelation
    {
        [$type, $id] = $this->getMorphs($this->relationConfig->getName());

        if (empty($this->parentEntry->{$type})) {
            return $this->newRelationQuery();
        } else {
            PlatformException::fail('yes related entry');
        }

        $query = $this->newRelationQuery();

        if ($this->relationConfig->hasPivotColumns()) {
            $query->withPivot($this->relationConfig->getPivotColumns());
        }

        return $query;
    }

//    protected function morphEagerTo($name, $type, $id, $ownerKey)
//    {
//        return $this->newMorphTo(
//            $this->newQuery()->setEagerLoads([]), $this, $id, $ownerKey, $type, $name
//        );
//    }
//
//    /**
//     * Define a polymorphic, inverse one-to-one or many relationship.
//     *
//     * @param  string  $target
//     * @param  string  $name
//     * @param  string  $type
//     * @param  string  $id
//     * @param  string  $ownerKey
//     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
//     */
//    protected function morphInstanceTo($target, $name, $type, $id, $ownerKey)
//    {
//        $instance = $this->newRelatedInstance(
//            static::getActualClassNameForMorph($target)
//        );
//
//        return $this->newMorphTo(
//            $instance->newQuery(), $this, $id, $ownerKey ?? $instance->getKeyName(), $type, $name
//        );
//    }

    protected function getMorphs($name)
    {
        return [$name.'_type', $name.'_id'];
    }
}
