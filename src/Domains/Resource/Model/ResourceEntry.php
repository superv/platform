<?php

namespace SuperV\Platform\Domains\Resource\Model;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Queue\SerializesModels;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Model\MakesEntry;
use SuperV\Platform\Domains\Database\Model\Repository;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Relation\RelationFactory as RelationBuilder;
use SuperV\Platform\Domains\Resource\Relation\RelationModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ResourceEntry extends Entry
{
    use Restorable;

    use SerializesModels {
        SerializesModels::__sleep as parentSleep;
    }



    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    protected $resourceConfig;

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
            if (! $this->isPlatformResource()) {
                if ($field = $this->getResource()->getField($relationName)) {
                    $fieldType = $field->getFieldType();

                    return $fieldType->newQuery($this)->getResults()->first();
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

        if (! method_exists($this, $name) && ! in_array($name, ['create', 'first', 'find'])) {
            if ($relation = $this->getRelationshipFromConfig($name)) {
                return $relation;
            } elseif ($relation = superv('relations')->get($this->getHandle().'.'.$name)) {
                return $relation->newQuery();
            } elseif (! $this->isPlatformResource()) {
                if ($field = $this->getResource()->getField($name)) {
                    $fieldType = $field->getFieldType();

                    return $fieldType->newQuery($this);
                }
            }
        }

        return parent::__call($name, $arguments);
    }

    public function getConnectionName()
    {
        return parent::getConnectionName();
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
        if (optional($this->getResourceConfig())->isRestorable()) {
            static::addGlobalScope(new SoftDeletingScope());
        } else {
            return parent::newQuery()->withoutGlobalScopes();
        }

        return parent::newQuery();
    }

    public function getForeignKey()
    {
//        if (! $resource = $this->getResource()) {
//            return parent::getForeignKey();
//        }

        return $this->getResourceConfig()->getResourceKey().'_id';
    }

    public function getMorphClass()
    {
        return $this->getTable();
    }

    public function __sleep()
    {
        $this->resource = null;

        return $this->parentSleep();
    }

    public function getResourceConfig()
    {
        if (! $this->resourceConfig) {
            $this->resourceConfig = ResourceConfig::find($this->getResourceIdentifier());
        }

        return $this->resourceConfig;
    }

    public function getResourceDsn()
    {
        return sprintf("%s@%s://%s", 'database', $this->getConnectionName(), $this->getTable());
    }

    public function getRelationshipFromConfig($name)
    {
        if (! $relation = $this->resolveRelation($name)) {
            if (
                snake_case($name) === $name ||
                ! $relation = $this->resolveRelation(snake_case($name))
            ) {
                return null;
            }
        }

        return $relation->newQuery();
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public function getResource()
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->getResourceConfig()->getIdentifier());
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

    public function isPlatformResource()
    {
        return starts_with($this->getHandle(), 'sv_');
    }

    public function route($route, array $params = [])
    {
        return $this->getResource()->route($route, $this, $params);
    }

    public function getField(string $name): ?Field
    {
        $field = $this->getResource()->getField($name);

        return $field;
//        return $field->setWatcher($this);
    }

    /**
     * @param string $resourceIdentifier
     */
    public function setResourceIdentifier(string $resourceIdentifier): void
    {
        $this->resourceIdentifier = $resourceIdentifier;
    }

    public static function make(Resource $resource)
    {
        $model = new AnonymousModel();
        $model->setTable($resource->config()->getDriver()->getParam('table'));
        $model->setConnection($resource->config()->getDriver()->getParam('connection'));
        $model->setKeyName($resource->getKeyName());
        $model->setResourceIdentifier($resource->config()->getIdentifier());

        return $model;
    }

    protected function resolveRelation($name)
    {
        if (! $relation = RelationModel::fromCache($this->getResourceIdentifier(), $name)) {
            return null;
        }

        $relation = RelationBuilder::resolveFromRelationEntry($relation);
        if ($relation instanceof AcceptsParentEntry) {
            $relation->acceptParentEntry($this);
        }

        return $relation;
    }
}
