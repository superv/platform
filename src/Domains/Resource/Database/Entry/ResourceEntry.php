<?php

namespace SuperV\Platform\Domains\Resource\Database\Entry;

use Illuminate\Queue\SerializesModels;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;

class ResourceEntry extends Entry
{
    use Restorable;

    use SerializesModels {
        SerializesModels::__sleep as parentSleep;
    }

    public function getRelationshipFromConfig($name)
    {
        if ($relation = $this->resolveRelation($name)) {
            return $relation->newQuery();
        }
        // Try again with the snake case version
        if (snake_case($name) !== $name) {
            if ($relation = $this->resolveRelation(snake_case($name))) {
                return $relation->newQuery();
            }
        }

        return null;
    }

    public function __sleep()
    {
        $this->resource = null;

        return $this->parentSleep();
    }

    public function __call($name, $arguments)
    {
        /**
         *  Responds to $entry->get{Relation}
         */
        if (starts_with($name, 'get')) {
            $relationName = snake_case(str_replace_first('get', '', $name));
            if ($relation = $this->resolveRelation($relationName)) {
                if ($targetModel = $relation->getRelationConfig()->getTargetModel()) {
                    /** @var \SuperV\Platform\Domains\Database\Model\Entry $relatedEntry */
                    if ($relatedEntry = $relation->newQuery()->getResults()->first()) {
                        $targetModelInstance = new $targetModel;

                        if ($targetModelInstance instanceof Repository) {
                            return $targetModelInstance->resolve($relatedEntry, $this);
                        }
                    }
                }
            }
            if ($field = $this->getResource()->getField($relationName)) {
                $fieldType = $field->type();

                return $fieldType->newQuery($this)->getResults()->first();
            }
        }

        /**
         *  Dynamic Relations
         */
        if (! method_exists($this, $name) && ! in_array($name, ['create', 'first', 'find', 'hydrate'])) {
//            if (in_array($name, $this->relationKeys)) {
            if ($relation = $this->getRelationshipFromConfig($name)) {
                $svRelation = $this->resolveRelation($name);

                return $svRelation->newQuery();
            } elseif ($relation = superv('relations')->get($this->getResourceIdentifier().'.'.$name)) {
                if ($relation instanceof AcceptsParentEntry) {
                    $relation->acceptParentEntry($this);
                }

                return $relation->newQuery();
            } elseif ($field = $this->getResource()->getField($name)) {
                $fieldType = $field->type();

                if ($fieldType instanceof ProvidesRelationQuery) {
                    return $fieldType->getRelationQuery($this);
                }

                return $fieldType->newQuery($this);
            }
        }

        return parent::__call($name, $arguments);
    }

    protected function resolveRelation($name)
    {
        if (! $relation = RelationModel::fromCache($this->getResourceIdentifier(), $name)) {
            return null;
        }

        $relation = RelationBuilder::resolveFromRelationEntry($relation);
        if ($relation instanceof AcceptsParentEntry) {
            $relation->acceptParentEntry($this);
        }

        return $relation;
    }
}
