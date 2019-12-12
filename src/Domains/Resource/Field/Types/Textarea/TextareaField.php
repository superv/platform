<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Textarea;

use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Support\Composer\Payload;

class TextareaField extends FieldType implements RequiresDbColumn
{
    protected $component = 'textarea';

    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
    }

    protected function composer()
    {
        return function (Payload $payload) {
            if ($this->getConfigValue('rich') === true) {
                $payload->set('meta.rich', true);
            }
        };
    }

    public function driverCreating(
        DriverInterface $driver,
        \SuperV\Platform\Domains\Resource\Builder\FieldBlueprint $blueprint
    ) {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getColumnName(), 'text');
        }
    }
}
