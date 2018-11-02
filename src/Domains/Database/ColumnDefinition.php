<?php

namespace SuperV\Platform\Domains\Database;

use SuperV\Platform\Domains\Resource\Relation\RelationConfig;

/**
 * Class ColumnDefinition
 * @method ColumnDefinition nullable($value = true) Allow NULL values to be inserted into the column
 *  @method ColumnDefinition ignore($value = true)
 *  @method ColumnDefinition fieldType($type)
 *  @method ColumnDefinition config(array $config)
 */
class ColumnDefinition extends \Illuminate\Database\Schema\ColumnDefinition
{
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

    public function getRelation(): RelationConfig
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
}