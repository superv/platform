<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Queue\SerializesModels;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;

class ResourceEntry extends Entry
{
    use Restorable;

    use SerializesModels {
        SerializesModels::__sleep as parentSleep;
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function (ResourceEntry $entry) {
            if (! starts_with($entry->getTable(), 'sv_')) {
                if ($entry->getResource()->config()->hasUuid() && is_null($entry->uuid)) {
                    $entry->setAttribute('uuid', uuid());
                }
            }
        });
    }

    public function __call($name, $arguments)
    {
        /**
         *  Responds to $entry->get{Relation}
         */
        if (starts_with($name, 'get')) {
            $relationName = snake_case(str_replace_first('get', '', $name));
            sv_console($relationName);
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
//            if (! $this->isPlatformResource()) {
                if ($field = $this->getResource()->getField($relationName)) {
                    $fieldType = $field->getFieldType();

                    return $fieldType->newQuery($this)->getResults()->first();
                }
//            }
        }


        /**
         *  Dynamic Relations
         */
        if (! method_exists($this, $name) && ! in_array($name, ['create', 'first', 'find', 'hydrate'])) {
            if ($relation = $this->getRelationshipFromConfig($name)) {
                return $relation;
            } elseif ($relation = superv('relations')->get($this->getResourceIdentifier().'.'.$name)) {
                return $relation->newQuery();
            } else { // if (! $this->isPlatformResource()) {
                if ($field = $this->getResource()->getField($name)) {
                    $fieldType = $field->getFieldType();

                    return $fieldType->newQuery($this);
                }
            }
        }

        return parent::__call($name, $arguments);
    }


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

    public function __sleep()
    {
        $this->resource = null;

        return $this->parentSleep();
    }


//    public function isPlatformResource()
//    {
//        return starts_with($this->getTable(), 'sv_');
//    }
}
