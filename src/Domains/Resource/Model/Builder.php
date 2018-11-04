<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * Get the relation instance for the given relation name.
     *
     * @param  string $name
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getRelation($name)
    {
        // We want to run a relationship query without any constrains so that we will
        // not have to remove these where clauses manually which gets really hacky
        // and error prone. We don't want constraints because we add eager ones.
        $relation = Relation::noConstraints(function () use ($name) {
            $instance = $this->getModel()->newInstance();
            if (method_exists($instance, $name)) {
                return $instance->$name();
            }

            if ($relation = $instance->getRelationshipFromConfig($name)) {
                return $relation;
            }

            throw RelationNotFoundException::make($this->getModel(), $name);
        });

        $nested = $this->relationsNestedUnder($name);

        // If there are nested relationships set on the query, we will put those onto
        // the query instances so that they can be handled after this relationship
        // is loaded. In this way they will all trickle down as they are loaded.
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }

        return $relation;
    }
}