<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Exceptions\PlatformException;

class FieldFactory
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $fieldEntry;

    /**
     * @var array
     */
    protected $params;

    protected $flags = ['searchable', 'unique', 'required', 'nullable'];

    public static function createFromEntry(FieldModel $entry): Field
    {
        $factory = new static;
        $factory->params = $entry->toArray();

        return $factory->create();
    }

    public static function createFromArray(array $params): Field
    {
        $factory = new static;
        $factory->params = $params;

        return $factory->create();
    }

    protected function create(): Field
    {
        if (! isset($this->params['name'])) {
            PlatformException::fail('Missing parameter [name] for field');
        }

        $class = FieldType::resolveClass( $this->params['type']);
        /** @var \SuperV\Platform\Domains\Resource\Field\Field $field */
        $field = new $class($this->params);

        if (! $field->hasFlag('nullable')) {
            $field->addFlag('required');
        }

        return $field;
    }
}