<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use SuperV\Platform\Domains\Entry\EntryModelV2;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceEntryModel extends EntryModelV2
{
    /** @var Resource */
    public static $resource;

    public function getRelationshipFromConfig($name)
    {
        if (! $relation = static::$resource->getRelation($name)) {
            return null;
        }

        return $relation->newQuery();

        if ($relation->getType()->isBelongsTo()) {

//            if ($resource = $relation->getConfigValue('related_resource')) {
//                $query = Resource::of($resource)->resolveModel()->newQuery();
//            } elseif ($model = $relation->getConfigValue('related_model')) {
//                $query =  $model::query()->newQuery();
//            }

            $query = $relation->newQuery();

            return $query;

            return new BelongsTo(
                $query,
                $this,
                $relation->getConfig()->getForeignKey(),
                'id',
                $name
            );
        }
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    public function newInstance($attributes = [], $exists = false)
    {
        return parent::newInstance($attributes, $exists);
    }

    /**
     * @param Resource $resource
     */
    public static function setResource(Resource $resource): void
    {
        self::$resource = $resource;
    }

    public static function make(Resource $resource)
    {
        $model = new class extends ResourceEntryModel
        {
            public $timestamps = false;

            public function getMorphClass()
            {
                return static::$resource->getSlug();
            }

            public static function __callStatic($method, $parameters)
            {
                $static = (new static);
                $static->setTable($static::$resource->getSlug());

                return $static->$method(...$parameters);
            }
        };
        $model::setResource($resource);
        $model->setTable($resource->getSlug());

        return $model;
    }
}