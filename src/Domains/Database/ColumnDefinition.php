<?php

namespace SuperV\Platform\Domains\Database;

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

    public function getConfig()
    {
        return $this->config ?? [];
    }

    public function relation(array $relation):self
    {
        $this->fieldType = 'relation';
        $this->relation = $relation;

        return $this;
    }

    public function getRelation()
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