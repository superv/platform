<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $model;

    /**
     * Get the relation instance for the given relation name.
     *
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getRelation($name)
    {
        // We want to run a relationship query without any constrains so that we will
        // not have to remove these where clauses manually which gets really hacky
        // and error prone. We don't want constraints because we add eager ones.
        $relation = Relation::noConstraints(function () use ($name) {
            /**
             *      <superV override=true>
             */
            $instance = $this->getModel()->newInstance();
            if (method_exists($instance, $name)) {
                return $instance->$name();
            }

            if ($relation = $instance->getRelationshipFromConfig($name)) {
                return $relation;
            }

            throw new RuntimeException(sprintf("Call to undefined relationship [%s] on resource [%s].", $name, $this->getModel()->getResourceIdentifier()));
            /**
             *      </superV>
             */

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

    /**
     * Get the model instance being queried.
     *
     * @return  \SuperV\Platform\Domains\Resource\Model\ResourceEntry|static
     */
    public function getModel()
    {
        return $this->model;
    }
}