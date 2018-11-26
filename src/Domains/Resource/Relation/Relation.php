<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation implements AcceptsParentEntry
{
    use Hydratable;

    /** @var string */
    protected $name;

    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationType */
    protected $type;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $parentEntry;

    /** @var RelationConfig */
    protected $config;

    abstract protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation;

    public function acceptParentEntry(EntryContract $entry)
    {
        $this->parentEntry = $entry;
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

    protected function newRelatedInstance(): ?EntryContract
    {
        if ($model = $this->config->getRelatedModel()) {
            return new $model;
        } elseif ($handle = $this->config->getRelatedResource()) {
            return Resource::of($handle)->newEntryInstance();
        }

        throw new PlatformException('Related resource/model not found');
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource; */
    public function getRelatedResource(): Resource
    {
        return Resource::of($this->config->getRelatedResource());
    }

    protected function getRelatedEntry(): ?EntryContract
    {
        if ($entry = $this->newQuery()->first()) {
            return $entry;
        }

        return $this->newQuery()->make();
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

    public function getParentEntry()
    {
        return $this->parentEntry;
    }

    public function getParentResourceHandle(): string
    {
        return $this->parentEntry->getHandle();
    }

    public function route($name, EntryContract $entry)
    {
        return route('relation.'.$name,
            [
                'id'    => $entry->getId(),
                'resource' => $entry->getTable(),
                'relation' => $this->getName(),
            ]
        , false);
    }

    public function indexRoute(EntryContract $entry)
    {
        return $this->route('index', $entry);
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