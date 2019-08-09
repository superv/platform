<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Support\Concerns\Hydratable;

class ResourceConfig
{
    use Hydratable;

    protected $table;

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

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    public function getResourceKey()
    {
        if ($this->resourceKey) {
            return $this->resourceKey;
        }

//        if ($this->resource) {
//            return str_singular($this->resource->getHandle());
//        }

        if ($this->table) {
            return str_singular($this->table);
        }

        return null;
    }

    public function resourceKey($resourceKey)
    {
        $this->resourceKey = $resourceKey;

        return $this;
    }

    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getHandle()
    {
        return $this->table;
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

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getLabel()
    {
        return $this->label ?? ucwords(str_replace('_', ' ', $this->table));
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

    public function toArray(): array
    {
        $attributes = [];
        foreach ($this as $key => $value) {
            $attributes[snake_case($key)] = $value;
        }

        return array_except($attributes, 'resource');
    }

    public static function make(array $config, $overrideDefault = true)
    {
        return (new static)->hydrate($config, $overrideDefault);
    }
}
