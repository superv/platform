<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Select;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;

class SelectType extends FieldType implements
    RequiresDbColumn,
    ProvidesFilter,
    ProvidesFieldComponent
{
    protected $handle = 'select';

    protected $component = 'sv_select_field';

    public function handleDatabaseDriver(DatabaseDriver $driver, FieldBlueprint $blueprint, array $options = [])
    {
        $driver->getTable()->addColumn($this->getColumnName(), 'string', $options);
    }

    public function makeFilter(?array $params = [])
    {
        return SelectFilter::make($this->getFieldHandle(), $this->field->getLabel())
                           ->setOptions($this->getOptions())
                           ->setDefaultValue($params['default_value'] ?? null);
    }

    public function getOptions()
    {
        return $this->getConfigValue('options', []);
    }

    public function getComponentName(): string
    {
        return $this->component;
    }

    public static function parseOptions(array $options = [])
    {
        if (! empty($options) && ! is_array(array_first($options))) {
            return array_map(function ($value) use ($options) {
                return ['value' => $value, 'text' => $options[$value]];
            }, array_keys($options));
        }

        return $options;
    }
}
