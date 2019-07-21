<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ResourceEntry extends Entry
{
    use Restorable;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public $timestamps = false;

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
        }

        return parent::__call($name, $arguments);
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function newQuery()
    {
        if (optional($this->getResource())->isRestorable()) {
            static::addGlobalScope(new SoftDeletingScope());
        }

        return parent::newQuery();
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

    public function getForeignKey()
    {
        return $this->getResource()->config()->getResourceKey().'_id';
    }

    public function getMorphClass()
    {
        return $this->getTable();
    }

    public function getRelationshipFromConfig($name)
    {
        if (! $relation = $this->resolveRelation($name)) {
            if (! $relation = $this->resolveRelation(snake_case($name))) {
                return null;
            }
        }

        return $relation->newQuery();
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public function getResource()
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->getHandle());
        }

        return $this->resource;
    }

    public function setResource(Resource $resource): ResourceEntry
    {
        $this->resource = $resource;

        return $this;
    }

    public function getHandle(): string
    {
        return $this->getTable();
    }

    public function route($route)
    {
        return $this->getResource()->route($route, $this);
    }

    public function getField(string $name): ?Field
    {
        $field = $this->getResource()->getField($name);

        return $field->setWatcher($this);
    }

    public static function make(Resource $resource)
    {
        $model = new class extends ResourceEntry
        {
            public $timestamps = false;

            /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
            protected $resourceConfig;

            public function setResourceConfig($config)
            {
                $this->resourceConfig = $config;
            }

            public function setTable($table)
            {
                return $this->table = $table;
            }

            public function getKeyName()
            {
                if ($this->resourceConfig) {
                    return $this->resourceConfig->getKeyName();
                }

                return parent::getKeyName();
            }
        };

        $model->setTable($resource->getHandle());
        $model->setResourceConfig($resource->config());

        return $model;
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
