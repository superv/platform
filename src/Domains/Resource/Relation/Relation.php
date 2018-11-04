<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Contracts\HasResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation implements HasResource
{
    use Hydratable;

    /** @var string */
    protected $name;

    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationType */
    protected $type;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var RelationConfig */
    protected $config;

    public function newQuery()
    {
        $instance = $this->newRelatedInstance();

        $query = $this->newRelationQuery($instance);

        if ($this->config->hasPivotColumns()) {
            $query->withPivot($this->config->getPivotColumns());
        }

        return $query;
    }

    protected function newRelatedInstance()
    {
        if ($table = $this->config->getRelatedResource()) {
            return Resource::of($table)->resolveModel();
        } elseif ($model = $this->config->getRelatedModel()) {
            return new $model;
        }

        throw new PlatformException('Related resource/model not found');
    }
//
//    /**
//     * @param $instance
//     * @return \Illuminate\Database\Eloquent\Relations\HasMany
//     */
//    protected function newHasMany($instance)
//    {
//        return new HasMany(
//            $instance->newQuery(),
//            $this->resource->getEntry(),
//            $this->config->getForeignKey(),
//            $this->resource->getEntry()->getKeyName()
//        );
//    }
//
//    /**
//     * @param $instance
//     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
//     */
//    protected function newMorphToMany($instance)
//    {
//        return new MorphToMany(
//            $instance->newQuery(),
//            $this->parentModel,
//            $this->relationEntry->getMorphName(),
//            $this->relationEntry->getPivotTable(),
//            $this->relationEntry->getPivotForeignKey(),
//            $this->relationEntry->getPivotRelatedKey(),
//            $this->parentModel->getKeyName(),
//            $instance->getKeyName()
//        );
//    }
//
//    protected function newBelongsTo($instance)
//    {
//        return new BelongsTo(
//            $instance->newQuery(),
//            $this->resource->getEntry(),
//            $this->config->getForeignKey(),
//            'id',
//            $this->getName()
//        );
//    }

//    /**
//     * @param $instance
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
//     */
//    protected function newBelongsToMany($instance)
//    {
//        return new BelongsToMany(
//            $instance->newQuery(),
//            $this->resource->getEntry(),
//            $this->config->getPivotTable(),
//            $this->config->getPivotForeignKey(),
//            $this->config->getPivotRelatedKey(),
//            $this->resource->getEntry()->getKeyName(),
//            $instance->getKeyName()
//        );
//    }

    abstract  protected function newRelationQuery($instance);

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): RelationType
    {
        return $this->type;
    }

    /**
     * @param \SuperV\Platform\Domains\Resource\Relation\RelationType $type
     */
    public function setType($type): void
    {
        $this->type = is_string($type) ? new RelationType($type) : $type;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function getConfig(): RelationConfig
    {
        return $this->config;
    }

    public static function fromEntry(RelationModel $entry): self
    {
        $relation = new static;

        $relation->hydrate($entry->toArray());

        $relation->config = RelationConfig::create($relation->type, $relation->config);

        return $relation;
    }

    public static function resolve($type)
    {
        $class = static::resolveClass($type);

        return new $class(new RelationModel());
    }

    public static function resolveClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Relation\Types';

        $class = $base."\\".studly_case($type);

        return $class;
    }
}