<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Query\Builder as QueryBuilder;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavedEvent;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceEntryModel extends EntryModel
{
    protected static function boot()
    {
        parent::boot();

        static::saving(function(ResourceEntryModel $entry) {
            EntrySavingEvent::dispatch($entry);
        });

        static::saved(function(ResourceEntryModel $entry) {
            EntrySavedEvent::dispatch($entry);
        });

    }

    /**
     * Wrap the entry model with parent resource
     *
     * @return Resource
     */
    public function wrap(): Resource
    {
        return Resource::of($this->getTable(), false)->setEntry($this);
    }

    public function getRelationshipFromConfig($name)
    {
        $relation = RelationModel::fromCache($this->getTable(), $name);

        $relation = RelationBuilder::resolveFromRelationEntry($relation);

        $relation->setParentEntry($this);

        return $relation->newQuery();
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

    /** @return self */
    public function newInstance($attributes = [], $exists = false)
    {
        return parent::newInstance($attributes, $exists);
    }

    public static function make($resourceHandle)
    {
        $model = new class extends ResourceEntryModel
        {
            public $timestamps = false;

            /**
             * Resource handle
             *
             * @var string
             */
            public static $resource;

            public function setTable($table)
            {
                return $this->table = static::$resource = $table;
            }

            public function getMorphClass()
            {
                return $this->getTable();
            }

            public static function __callStatic($method, $parameters)
            {
                $static = (new static);
                $static->setTable($static::$resource);

                return $static->$method(...$parameters);
            }
        };
        $model->setTable($resourceHandle);

        return $model;
    }
}