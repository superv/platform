<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Support\Concerns\Hydratable;

class ResourceConfig
{
    use Hydratable;

    protected $name;

    protected $namespace;

    protected $identifier;

    protected $hasUuid;

    protected $label;

    protected $singularLabel;

    protected $model;

    protected $resourceKey;

    protected $ownerKey;

    protected $keyName;

    protected $entryLabelField;

    protected $entryLabel;

    protected $nav;

    protected $attributes;

    protected $restorable;

    protected $sortable;

    /** @var \SuperV\Platform\Domains\Resource\ResourceDriver */
    protected $driver;

    protected function __construct(array $attributes = [], $overrideDefault = true)
    {
        if (! empty($attributes)) {
            if ($driver = array_get($attributes, 'driver')) {
                $attributes['driver'] = new ResourceDriver($driver);
            }
            $this->hydrate($attributes, $overrideDefault);
        }
    }

    public function getResourceKey()
    {
        if ($this->resourceKey) {
            return $this->resourceKey;
        }

//        if ($this->resource) {
//            return str_singular($this->resource->getHandle());
//        }

        if ($this->getName()) {
            return str_singular($this->getName());
        }

        return null;
    }

    public function resourceKey($resourceKey)
    {
        $this->resourceKey = $resourceKey;

        return $this;
    }

    public function getIdentifier()
    {
        if ($this->identifier) {
            return $this->identifier;
        }

        return $this->getNamespace().'::'.$this->getName();
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getHandle()
    {
        return $this->getIdentifier();
    }

    public function label($label)
    {
        $this->label = $label;

        if (! $this->resourceKey) {
            $this->resourceKey(str_slug(str_singular($label), '_'));
        }
    }

    public function fill(array $attributes = [])
    {
        $this->hydrate($attributes);

//        foreach ($attributes as $key => $value) {
//            $this->attributes[$key] = $value;
//        }
    }

    public function getKeyName($default = 'id')
    {
        return $this->keyName ?? $default;
    }

    public function keyName($keyName)
    {
        $this->keyName = $keyName;

        return $this;
    }

    public function getLabel()
    {
        return $this->label ?? ucwords(str_replace('_', ' ', $this->getName()));
    }

    public function getModel()
    {
        return $this->model;
    }

    public function model($model)
    {
        $this->model = $model;

        return $this;
    }

    public function entryLabel($entryLabel)
    {
        $this->entryLabel = $entryLabel;

        return $this;
    }

    public function getEntryLabel($default = null)
    {
        return $this->entryLabel ?? $default;
    }

    public function entryLabelField($fieldName)
    {
        $this->entryLabelField = $fieldName;

        return $this;
    }

    public function getEntryLabelField()
    {
        return $this->entryLabelField;
    }

    public function hasUuid(): bool
    {
        return $this->hasUuid ?? false;
    }

    public function setHasUuid(bool $hasUuid): ResourceConfig
    {
        $this->hasUuid = $hasUuid;

        return $this;
    }

    public function nav($nav)
    {
        $this->nav = $nav;

        return $this;
    }

    public function isRestorable(): bool
    {
        return $this->restorable ?? false;
    }

    public function isSortable(): bool
    {
        return $this->sortable ?? false;
    }

    public function getNav()
    {
        return $this->nav;
    }

    public function restorable(bool $restorable = true): ResourceConfig
    {
        $this->restorable = $restorable;

        return $this;
    }

    public function sortable(bool $sortable): ResourceConfig
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function singularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;

        return $this;
    }

    public function getSingularLabel()
    {
        return $this->singularLabel;
    }

    public function ownerKey($ownerKey)
    {
        $this->ownerKey = $ownerKey;

        return $this;
    }

    public function getOwnerKey()
    {
        return $this->ownerKey;
    }

    /**
     * @return \SuperV\Platform\Domains\Resource\ResourceDriver
     */
    public function getDriver(): \SuperV\Platform\Domains\Resource\ResourceDriver
    {
        return $this->driver;
    }

    public function getDriverParam($key)
    {
        return $this->getDriver()->getParam($key);
    }

    public function getTable()
    {
        return $this->getDriver()->getParam('table');
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function toArray(): array
    {
        $attributes = [];
        foreach ($this as $key => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }
            $attributes[snake_case($key)] = $value;
        }

        return array_except($attributes, 'resource');
    }

    public static function make(array $config = [], $overrideDefault = true)
    {
        return (new static($config, $overrideDefault));
    }

    public static function find($identifier)
    {
        $resourceEntry = ResourceModel::query()->where('identifier', $identifier)->first();

        return new ResourceConfig($resourceEntry->config);
    }
}
