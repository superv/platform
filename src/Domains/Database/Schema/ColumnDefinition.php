<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Visibility\Visibility;

/**
 * Class ColumnDefinition
 * @method ColumnDefinition nullable($value = true) Allow NULL values to be inserted into the column
 * @method ColumnDefinition ignore($value = true)
 * @method ColumnDefinition fieldType($type)
 * @method ColumnDefinition rules(array $rules)
 * @method ColumnDefinition config(array $config)
 */
class ColumnDefinition extends \Illuminate\Database\Schema\ColumnDefinition
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceBlueprint */
    protected $blueprint;

    public function __construct(ResourceBlueprint $blueprint, $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        $this->blueprint = $blueprint;
    }

    public function entryLabel()
    {
        $this->blueprint->entryLabel('{'.$this->name.'}');
    }

    public function isRequired()
    {
        return ! (bool)$this->nullable;
    }

    public function isUnique()
    {
        return (bool)$this->unique;
    }

    public function isSearchable()
    {
        return (bool)$this->searchable;
    }

    public function isTitleColumn()
    {
        return $this->titleColumn;
    }

    public function getDefaultValue()
    {
        return $this->default;
    }

    public function getFieldType()
    {
        return $this->fieldType;
    }

    public function getFieldName()
    {
        return $this->fieldName ?? $this->name;
    }

    public function getConfig()
    {
        return $this->config ?? [];
    }

    public function showOnIndex()
    {
        return $this->addFlag('table.show');
    }

    public function showOnHeader()
    {
        return $this->addFlag('header.show');
    }

    public function addFlag($flag)
    {
        $flags = $this->config['flags'] ?? [];
        $flags[] = $flag;

        $config = $this->config;
        $config['flags'] = $flags;
        $this->config = $config;

        return $this;
    }

    public function getRules()
    {
        if (! $this->rules) {
            return [];
        }

        if (is_string($this->rules)) {
            return explode('|', $this->rules);
        }

        return $this->rules;
    }

    public function relation(RelationConfig $relation): self
    {
        $this->fieldType = $relation->getType();
        $this->relation = $relation;

        return $this;
    }

    public function getRelationConfig(): RelationConfig
    {
        return $this->relation;
    }

    public function options(array $options): self
    {
        $config = $this->getConfig();
        $config['options'] = $options;
        $this->config = $config;

        return $this;
    }

    public function visibility(Closure $callback)
    {
        $visibility = new Visibility();
        $callback($visibility);
    }
}