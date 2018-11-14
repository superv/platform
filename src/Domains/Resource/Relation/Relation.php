<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation implements NeedsEntry
{
    use Hydratable;

    /** @var string */
    protected $name;

    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationType */
    protected $type;

    /** @var ResourceEntry */
    protected $parentEntry;

    /** @var RelationConfig */
    protected $config;

    /** @var ResourceEntry */
    protected $resourceEntry;

    public function newQuery(): EloquentRelation
    {
        $instance = $this->newRelatedInstance();

        $query = $this->newRelationQuery($instance);

        if ($this->config->hasPivotColumns()) {
            $query->withPivot($this->config->getPivotColumns());
        }

        return $query;
    }

    protected function newRelatedInstance(): ?ResourceEntry
    {
        if ($model = $this->config->getRelatedModel()) {
            return new ResourceEntry(new $model);
        } elseif ($table = $this->config->getRelatedResource()) {
            return ResourceEntry::newInstance($table);
        }

        throw new PlatformException('Related resource/model not found');
    }

    public function setEntry(ResourceEntry $entry)
    {
        $this->resourceEntry = $entry;
    }

    abstract protected function newRelationQuery(ResourceEntry $relatedEntryInstance): EloquentRelation;

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

    public function getParentEntry(): ?Model
    {
        return $this->parentEntry ? $this->parentEntry->getEntry() : $this->resourceEntry->getEntry();
    }

    public function setParentEntry(ResourceEntry $parentEntry): Relation
    {
        $this->parentEntry = $parentEntry;

        return $this;
    }

    public function getEntry(): ResourceEntry
    {
        return $this->resourceEntry;
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