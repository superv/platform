<?php

namespace SuperV\Platform\Domains\Database\Schema;

use Closure;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\Visibility\Visibility;

/**
 * Class ColumnDefinition
 * @method ColumnDefinition ignore($value = true)
 * @method ColumnDefinition fieldType($type)
 * @method ColumnDefinition rules(array|string $rules)
 * @method ColumnDefinition config(array $config)
 */
class ColumnDefinition extends \Illuminate\Database\Schema\ColumnDefinition
{
    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    protected $blueprint;

    public function __construct(ResourceConfig $blueprint, $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        $this->attributes['config'] = [];
        $this->attributes['rules'] = [];
        $this->attributes['flags'] = [];

        $this->blueprint = $blueprint;
    }

    public function entryLabel()
    {
        $this->blueprint->entryLabel('{'.$this->name.'}');
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
        return $this->config;
    }

    public function searchable()
    {
        return $this->addFlag('searchable');
    }

    public function required()
    {
        return $this->addFlag('required');
    }

    public function nullable()
    {
        $this->nullable = true;

        return $this->addFlag('nullable');
    }

    public function unique()
    {
        return $this->addFlag('unique');
    }

    public function default($value)
    {
        $this->offsetSet('default', $value);

        return $this->nullable();
    }

    public function unit($unit)
    {
        return $this->setConfigValue('unit', $unit);
    }

    public function showOnIndex()
    {
        return $this->addFlag('table.show');
    }

    public function showOnHeader()
    {
        return $this->addFlag('header.show');
    }

    public function hideOnForms()
    {
        return $this->addFlag('form.hide');
    }

    public function addFlag($flag)
    {
        $flags = $this->flags;
        $flags[] = $flag;
        $this->flags = $flags;

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
//        $config = $this->getConfig();
//        $config['options'] = $options;
//        $this->config = $config;

        return $this->setConfigValue('options', $options);
    }

    public function setConfigValue($key, $value)
    {
        $config = $this->getConfig();
        $config[$key] = $value;
        $this->config = $config;

        return $this;
    }

    public function visibility(Closure $callback)
    {
        $visibility = new Visibility();
        $callback($visibility);
    }

    public function setWithEvent($event, $foreignKey, $localKey)
    {
    }
}