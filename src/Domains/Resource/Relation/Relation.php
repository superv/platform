<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsParentResourceEntry;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry as ResourceEntryContract;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation implements AcceptsParentResourceEntry
{
    use Hydratable;

    /** @var string */
    protected $name;

    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationType */
    protected $type;

    /** @var \SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry */
    protected $parentResourceEntry;

    /** @var RelationConfig */
    protected $config;

    abstract protected function newRelationQuery(ResourceEntryContract $relatedEntryInstance): EloquentRelation;

    public function acceptParentResourceEntry(ResourceEntryContract $entry)
    {
        $this->parentResourceEntry = $entry;
    }

    public function newQuery(): EloquentRelation
    {
        $instance = $this->newRelatedInstance();

        $query = $this->newRelationQuery($instance);

        if ($this->config->hasPivotColumns()) {
            $query->withPivot($this->config->getPivotColumns());
        }

        return $query;
    }

    protected function newRelatedInstance(): ?ResourceEntryContract
    {
        if ($model = $this->config->getRelatedModel()) {
            return new ResourceEntry(new $model);
        } elseif ($table = $this->config->getRelatedResource()) {
            return ResourceEntry::newInstance($table);
        }

        throw new PlatformException('Related resource/model not found');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): RelationType
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = is_string($type) ? new RelationType($type) : $type;
    }

    public function getConfig(): RelationConfig
    {
        return $this->config;
    }

    public static function fromEntry(Entry $entry): self
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