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
    public static function attributesFor(string $handle): array
    {
        if (! $resourceEntry = ResourceModel::withSlug($handle)) {
            throw new PlatformException("Resource model entry not found for [{$handle}]");
        }

        $resourceId = $resourceEntry->getId();

        return array_merge($resourceEntry->toArray(), [
            'handle'            => $resourceEntry->getSlug(),
            'fields'            => function () use ($resourceEntry) {
                return $resourceEntry->getFields()
                                     ->map(function (FieldModel $fieldEntry) {
                                         $field = FieldFactory::createFromEntry($fieldEntry);

                                         return $field;
                                     })
                                     ->keyBy(function (Field $field) { return $field->getName(); });
            },
            'relations'         => function () use ($resourceEntry) {
                return $resourceEntry->getResourceRelations()
                                     ->map(function (RelationModel $relation) {
                                         $relation = (new RelationFactory)->make($relation);

                                         return $relation;
                                     })
                                     ->keyBy(function (Relation $relation) { return $relation->getName(); });
            },
            'relation_provider' => function (string $name, ?Entry $entry = null) use ($resourceId) {
                $relationEntry = RelationModel::query()
                                              ->where('resource_id', $resourceId)
                                              ->where('name', $name)
                                              ->first();

                $relation = (new RelationFactory)->make($relationEntry);
                if ($entry && $relation instanceof NeedsEntry) {
                    $relation->setEntry($entry);
                }

                return $relation;
            },
        ]);
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public static function make(string $handle)
    {
        return new Resource(static::attributesFor($handle));
    }
}