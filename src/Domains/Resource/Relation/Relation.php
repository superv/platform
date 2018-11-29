<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation implements AcceptsParentEntry, ProvidesQuery
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

    protected $flags = [];

    public function addFlag(string $flag)
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags);
    }

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
            return ResourceFactory::make($handle)->newEntryInstance();
        }

        throw new PlatformException('Related resource/model not found');
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource; */
    public function getRelatedResource(): Resource
    {
        return ResourceFactory::make($this->config->getRelatedResource());
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

    public function route($name, EntryContract $entry, array $params = [])
    {
        $params = array_merge([
            'id'       => $entry->getId(),
            'resource' => $entry->getTable(),
            'relation' => $this->getName(),
        ], $params);

        return route('relation.'.$name, $params, false);
    }

    public function indexRoute(EntryContract $entry)
    {
        return $this->route('index', $entry);
    }

    public function getPivotFields()
    {
        if (! $pivotColumns = $this->getConfig()->getPivotColumns()) {
            return [];
        }
        $pivotResource = ResourceFactory::make($this->getConfig()->getPivotTable());

        return $pivotResource->fields()
                             ->keyByName()
                             ->filter(function (Field $field) use ($pivotColumns) {
                                 return in_array($field->getColumnName(), $pivotColumns);
                             })
                             ->map(function (Field $field) {
                                 $field->setCallback('table.presenting', function (EntryContract $entry) use ($field) {
                                     if ($pivot = $entry->pivot) {
                                         return $pivot->{$field->getColumnName()};
                                     }
                                 });

                                 $field->showOnIndex();

                                 return $field;
                             });
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