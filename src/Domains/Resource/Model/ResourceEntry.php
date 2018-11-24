<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Query\Builder as QueryBuilder;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ResourceEntry extends Entry
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function getRelationshipFromConfig($name)
    {
        if ($relation = $this->resolveRelation($name)) {
            return $relation->newQuery();
        }
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

    public function getResource(): Resource
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->getHandle());
        }

        return $this->resource;
    }

    public function getHandle(): string
    {
        return $this->getTable();
    }

    public function route($route)
    {
        $base = 'sv/res/'.$this->getHandle();
        if ($route === 'edit') {
            return $base.'/'.$this->getId().'/edit';
        }
        if ($route === 'update') {
            return $base.'/'.$this->getId();
        }
        if ($route === 'delete') {
            return $base.'/'.$this->getId().'/delete';
        }
    }

    public function getField(string $name): ?Field
    {
        $field = $this->getResource()->getField($name);

        return $field->setWatcher($this);
    }

    public function getFieldType(string $name): ?FieldType
    {
        $field = $this->getField($name);

        $fieldType = $field->fieldType();
        if ($fieldType instanceof AcceptsEntry) {
            $fieldType->acceptEntry($this);
        }

        return $fieldType;
    }

    public function getLabel()
    {
        $label = $this->getResource()->getConfigValue('entry_label');

        return sv_parse($label, $this->toArray());
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'get')) {
            $relationName = snake_case(str_replace_first('get', '', $name));
            if ($relation = $this->resolveRelation($relationName)) {
                if ($targetModel = $relation->getConfig()->getTargetModel()) {
                    /** @var \SuperV\Platform\Domains\Database\Model\Entry $relatedEntry */
                    if ($relatedEntry = $relation->newQuery()->getResults()->first()) {
                        $targetModelInstance = new $targetModel;

                        if ($targetModelInstance instanceof Repository) {
                            return $targetModelInstance->resolve($relatedEntry, $this);
                        }
                    }
                }
            }
        } elseif (starts_with($name, 'make')) {
            $relationName = snake_case(str_replace_first('make', '', $name));
            if ($relation = $this->resolveRelation($relationName)) {
                if ($targetModel = $relation->getConfig()->getTargetModel()) {
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
        }

        return parent::__call($name, $arguments);
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

    public static function make($resourceHandle)
    {
        $model = new class extends ResourceEntry
        {
            public $timestamps = false;

            public function setTable($table)
            {
                return $this->table = $table;
            }

            public function getMorphClass()
            {
                return $this->getTable();
            }
        };
        $model->setTable($resourceHandle);

        return $model;
    }
}