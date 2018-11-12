<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Query\Builder as QueryBuilder;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavedEvent;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceEntryModel extends EntryModel implements Watcher
{
    protected $__form;

    public function getForm(): Form
    {
        return $this->__form;
    }

    public function setForm($_form): self
    {
        $this->__form = $_form;

        return $this;
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
        if (! $relation = RelationModel::fromCache($this->getTable(), $name)) {
            return null;
        }

        $relation = RelationBuilder::resolveFromRelationEntry($relation);

        $relation->setParentEntry(new ResourceEntry($this));

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

    protected static function boot()
    {
        parent::boot();

//        static::saving(function(ResourceEntryModel $entry) {
//            EntrySavingEvent::dispatch($entry);
//        });

        static::saved(function (ResourceEntryModel $entry) {
            EntrySavedEvent::dispatch($entry);
        });
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