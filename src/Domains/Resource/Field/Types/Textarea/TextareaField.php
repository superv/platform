<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Textarea;

use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class TextareaField extends FieldType implements
    RequiresDbColumn,
    ProvidesFieldComponent
{
    protected $handle = 'textarea';

    protected $component = 'sv_textarea_field';

    public function handleDatabaseDriver(DatabaseDriver $driver, FieldBlueprint $blueprint, array $options = [])
    {
        $driver->getTable()->addColumn($this->getColumnName(), 'text', $options);
    }

    public function getComponentName(): string
    {
        return $this->component;
    }
}
