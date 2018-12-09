<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Exceptions\PlatformException;

class RelationFactory
{
    protected $base = 'SuperV\Platform\Domains\Resource\Relation\Types';

    public function make($relation)
    {
        if ($relation instanceof RelationModel) {
            $relation = static::resolveFromRelationEntry($relation);
        }

        return $relation;
    }

    public static function resolveFromRelationEntry(Entry $entry): Relation
    {
        /** @var \SuperV\Platform\Domains\Resource\Relation\Relation $class */
        $class = Relation::resolveClass($entry->type);
        if (! class_exists($class)) {
            throw new PlatformException("Relation class not found for type ".$entry->type);
        }

        return $class::fromEntry($entry);
    }
}