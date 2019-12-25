<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Textarea;

use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class TextareaField extends FieldType implements
    RequiresDbColumn,
    ProvidesFieldComponent
{
    protected $handle = 'textarea';

    protected $component = 'sv_textarea_field';

    public function driverCreating(
        DriverInterface $driver,
        \SuperV\Platform\Domains\Resource\Builder\FieldBlueprint $blueprint
    ) {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getColumnName(), 'text');
        }
    }

    public function getComponentName(): string
    {
        return $this->component;
    }
}
