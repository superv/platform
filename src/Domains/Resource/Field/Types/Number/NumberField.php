<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Number;

use Closure;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class NumberField extends FieldType implements RequiresDbColumn, SortsQuery, HasAccessor
{
    protected $component = 'sv_number_field';

    protected $handle = 'number';

    protected function boot()
    {
//        $this->field->on('form.accessing', $this->accessor());
//        $this->field->on('form.mutating', $this->accessor());
//
//        $this->field->on('view.presenting', $this->accessor());
    }

    public function driverCreating(
        DriverInterface $driver,
        \SuperV\Platform\Domains\Resource\Builder\FieldBlueprint $blueprint
    ) {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getColumnName(), 'integer');
        }
    }

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function makeRules()
    {
        $rules = [];

        $type = $this->getConfigValue('type');
        if ($type === 'integer') {
            $rules[] = 'integer';
        } elseif ($type === 'decimal') {
            $rules[] = 'numeric';
        }
        if ($this->getConfigValue('unsigned') === true) {
            $rules[] = 'min:0';
        }

        return $rules;
    }

//    protected function accessor()
//    {
//        return function ($value) {
//            if ($this->getConfigValue('type') === 'decimal') {
//                return (float)number_format(
//                    $value,
//                    $this->getConfigValue('places'),
//                    $this->getConfigValue('dec_point', '.'),
//                    $this->getConfigValue('thousands_sep', '')
//                );
//            }
//
//            return (int)$value;
//        };
//    }

    public function getAccessor(): Closure
    {
        return function ($value) {
            if ($this->getConfigValue('type') === 'decimal') {
                return (float)number_format(
                    $value,
                    $this->getConfigValue('places'),
                    $this->getConfigValue('dec_point', '.'),
                    $this->getConfigValue('thousands_sep', '')
                );
            }

            return (int)$value;
        };
    }
}
