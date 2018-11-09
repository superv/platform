<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation
{
    use Hydratable;

    /** @var string */
    protected $name;

    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationType */
    protected $type;

    /** @var \SuperV\Platform\Domains\Resource\Model\Entry */
    protected $parentEntry;

    /** @var RelationConfig */
    protected $config;

    public function newQuery(): EloquentRelation
    {
        $instance = $this->newRelatedInstance();

        $query = $this->newRelationQuery($instance);

        if ($this->config->hasPivotColumns()) {
            $query->withPivot($this->config->getPivotColumns());
        }

        return $query;
    }

    protected function newRelatedInstance(): ?Entry
    {
        if ($table = $this->config->getRelatedResource()) {
            return Entry::newInstance($table);
        } elseif ($model = $this->config->getRelatedModel()) {
            return new Entry(new $model);
        }

        throw new PlatformException('Related resource/model not found');
    }

    abstract protected function newRelationQuery(Entry $relatedEntryInstance): EloquentRelation;

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

    public function setParentEntry(Entry $parentEntry): Relation
    {
        $this->parentEntry = $parentEntry;

        return $this;
    }

    public function getParentEntry(): ?Entry
    {
        return $this->parentEntry;
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