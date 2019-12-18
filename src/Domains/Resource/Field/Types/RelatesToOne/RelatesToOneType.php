<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class RelatesToOneType extends FieldType
{
    protected $handle = 'relates_to_one';

    public function driverCreating(DriverInterface $driver, FieldBlueprint $blueprint)
    {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getFieldHandle().'_id', 'integer');
        }
    }
}