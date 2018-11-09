<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Exceptions\PlatformException;

class ResourceFactory
{
    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public static function make(string $handle)
    {
        if (! $resourceEntry = ResourceModel::withSlug($handle)) {
            throw new PlatformException("Resource model entry not found for [{$handle}]");
        }

        $resourceId = $resourceEntry->getId();
        $attributes = array_merge($resourceEntry->toArray(), [
            'fields'            => function (?Entry $entry) use ($resourceEntry) {
                return $resourceEntry->getFields()
                                     ->map(function (FieldModel $fieldEntry) use ($entry) {
                                         $field = FieldFactory::createFromEntry($fieldEntry);
                                         if ($field instanceof NeedsEntry) {
                                             $field->setEntry($entry);
                                         }

                                         return $field;
                                     })
                                     ->keyBy(function (Field $field) { return $field->getName(); });
            },
//            'relations'         => function (?Entry $entry) use ($resourceEntry) {
//                return $resourceEntry->getResourceRelations()
//                                     ->map(function (RelationModel $relation) use ($entry) {
//                                         $relation = (new RelationFactory)->make($relation);
//                                         if ($relation instanceof NeedsEntry) {
//                                             $relation->setEntry($entry);
//                                         }
//
//                                         return $relation;
//                                     })
//                                     ->keyBy(function (Relation $relation) { return $relation->getName(); });
//            },
            'relation_provider' => function (string $name, Entry $entry) use ($resourceId) {
                $relationEntry = RelationModel::query()
                                              ->where('resource_id', $resourceId)
                                              ->where('name', $name)
                                              ->first();

                $relation = (new RelationFactory)->make($relationEntry);
                if ($relation instanceof NeedsEntry) {
                    $relation->setEntry($entry);
                }

                return $relation;
            },
        ]);

        return new Resource($attributes);
    }
}