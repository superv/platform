<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Boolean;

use Closure;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class BooleanType extends FieldType implements
    RequiresDbColumn,
    HasAccessor,
    HasModifier,
    SortsQuery,
    ProvidesFieldComponent
{
    protected $handle = 'boolean';

    protected $component = 'sv_boolean_field';

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function getAccessor(): Closure
    {
        return function ($value) {
            return ($value === 'false' || ! $value) ? false : true;
        };
    }

    public function getModifier(): Closure
    {
        return function ($value) {
            return ($value === 'false' || ! $value) ? false : true;
        };
    }

    public function handleDatabaseDriver(DatabaseDriver $driver, FieldBlueprint $blueprint, array $options = [])
    {
        $driver->getTable()->addColumn($this->getColumnName(), 'boolean', $options);
    }

    public function getComponentName(): string
    {
        return $this->component;
    }

    protected function formatValue($value)
    {
        return ($value === 'false' || ! $value) ? false : true;
    }
}
