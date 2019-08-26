<?php

namespace SuperV\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;

trait HasDynamicRelations
{
    public function getRelationshipFromConfig($name)
    {
        if (! $relation = $this->resolveRelation($name)) {
            if (
                snake_case($name) === $name ||
                ! $relation = $this->resolveRelation(snake_case($name))
            ) {
                return null;
            }
        }

        return $relation->newQuery();
    }

    public function __call($name, $arguments)
    {
        /**
         * Responds to $entry->get{Relation}
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
            if (! $this->isPlatformResource()) {
                if ($field = $this->getResource()->getField($relationName)) {
                    $fieldType = $field->getFieldType();

                    return $fieldType->newQuery($this)->getResults()->first();
                }
            }
        }

        /**
         * Responds to $entry->make{Relation}
         */
        if (starts_with($name, 'make')) {
            $relationName = snake_case(str_replace_first('make', '', $name));
            if ($relation = $this->resolveRelation($relationName)) {
                if ($targetModel = $relation->getRelationConfig()->getTargetModel()) {
                    /** @var \SuperV\Platform\Domains\Database\Model\Entry $relatedEntry */
                    if ($relation instanceof MakesEntry) {
                        if ($relatedEntry = $relation->make($arguments)) {
                            $targetModelInstance = new $targetModel;

                            if ($targetModelInstance instanceof Repository) {
                                return $targetModelInstance->make($relatedEntry, $this);
                            }
                        }
                    }
                }
            }
        }

        if ($relation = $this->getRelationshipFromConfig($name)) {
            return $relation;
        } elseif ($relation = superv('relations')->get($this->getHandle().'.'.$name)) {
            return $relation->newQuery();
        } elseif (! $this->isPlatformResource()) {
            if ($field = $this->getResource()->getField($name)) {
                $fieldType = $field->getFieldType();

                return $fieldType->newQuery($this);
            }
        }

        return parent::__call($name, $arguments);
    }

    protected function resolveRelation($name)
    {
        if (! $relation = RelationModel::fromCache($this->getTable(), $name)) {
            return null;
        }

        $relation = RelationBuilder::resolveFromRelationEntry($relation);
        if ($relation instanceof AcceptsParentEntry) {
            $relation->acceptParentEntry($this);
        }

        return $relation;
    }
}
