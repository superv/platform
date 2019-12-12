<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Builder\RelationBlueprint;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Relation implements AcceptsParentEntry, ProvidesQuery
{
    use Hydratable;
    use HasConfig;
    use FiresCallbacks;

    /** @var string */
    protected $name;

    /** @var \SuperV\Platform\Domains\Resource\Relation\RelationType */
    protected $type;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $parentEntry;

    /** @var RelationConfig */
    protected $relationConfig;

    protected $flags = [];

    abstract protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation;

    public function addFlag(string $flag)
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags);
    }

    public function acceptParentEntry(EntryContract $entry)
    {
        $this->parentEntry = $entry;
    }

    public function newQuery(): EloquentRelation
    {
        $instance = $this->newRelatedInstance();

        $query = $this->newRelationQuery($instance);

        if ($this->relationConfig->hasPivotColumns()) {
            $query->withPivot($this->relationConfig->getPivotColumns());
        }

        return $query;
    }

    protected function newRelatedInstance(): EntryContract
    {
        if ($model = $this->relationConfig->getRelatedModel()) {
            return new $model;
        } elseif ($handle = $this->relationConfig->getRelatedResource()) {
            return EntryRepository::for($handle)->newInstance();
        }

        PlatformException::fail('Related resource/model not found ['.$this->getName().' :: '.$this->getType().']');
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource; */
    public function getRelatedResource(): Resource
    {
        return ResourceFactory::make($this->relationConfig->getRelatedResource());
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

    public function getRelationConfig(): RelationConfig
    {
        return $this->relationConfig;
    }

    public function getParentEntry()
    {
        return $this->parentEntry;
    }

    public function route($name, EntryContract $entry, array $params = [])
    {
        $params = array_merge([
            'entry'    => $entry->getId(),
            'resource' => $entry->getResourceIdentifier(),
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
        if (! $pivotColumns = $this->getRelationConfig()->getPivotColumns()) {
            return [];
        }
        $pivotResource = ResourceFactory::make(
//            sprintf("%s.%s", $this->getRelationConfig()->getPivotNamespace(), $this->getRelationConfig()->getPivotTable())
            $this->getRelationConfig()->getPivotIdentifier()
        );

        return $pivotResource->fields()
                             ->keyByName()
                             ->filter(function (FieldInterface $field) use ($pivotColumns) {
                                 return in_array($field->getColumnName(), $pivotColumns);
                             })
                             ->map(function (FieldInterface $field) {
                                 $field->setCallback('table.presenting', function (EntryContract $entry) use ($field) {
                                     if ($pivot = $entry->pivot) {
                                         return $pivot->{$field->getColumnName()};
                                     }
                                 });

                                 $field->showOnIndex();

                                 return $field;
                             });
    }

    public function driverCreating(RelationBlueprint $blueprint, DriverInterface $driver)
    {
    }

    public static function fromEntry(RelationModel $entry): self
    {
        $relation = new static;

        $relation->hydrate($entry->toArray());

        $relation->relationConfig = RelationConfig::create($relation->type, $entry->config);

        if (! $relation->relationConfig->getName()) {
            $relation->relationConfig->relationName($relation->getName());
        }

        return $relation;
    }

    public static function fromArray(array $params): self
    {
        $relation = new static;

        $relation->hydrate($params);

        $relation->relationConfig = RelationConfig::create($relation->type, $params['config'] ?? []);

        if (! $relation->relationConfig->getName()) {
            $relation->relationConfig->relationName($relation->getName());
        }

        return $relation;
    }

    public static function resolveType($type)
    {
        $class = static::resolveTypeClass($type);

        return new $class(new RelationModel());
    }

    public static function resolveTypeClass($type)
    {
        $type = (string)$type;

        $base = 'SuperV\Platform\Domains\Resource\Relation\Types';

        $class = $base."\\".studly_case($type);

        // custom directory
        if (! class_exists($class)) {
            $class = $base."\\".studly_case($type)."\\".studly_case($type);
        }

        return $class;
    }
}
