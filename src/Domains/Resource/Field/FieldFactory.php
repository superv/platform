<?php

namespace SuperV\Platform\Domains\Resource\Field;

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

    public static function createFromEntry(FieldModel $entry, string $resolveFrom = null): Field
    {
        $factory = new static;
        $factory->params = $entry->toArray();

        return $factory->create($resolveFrom);
    }

    public static function createFromArray(array $params, string $resolveFrom = null): Field
    {
        $factory = new static;
        $factory->params = $params;

        return $factory->create($resolveFrom);
    }

    protected function create(string $resolveFrom = null): Field
    {
        if (! isset($this->params['name'])) {
            PlatformException::fail('Missing parameter [name] for field');
        }

        $fieldTypeClass = FieldType::resolveTypeClass($this->params['type']);

        /** @var \SuperV\Platform\Domains\Resource\Field\FieldType $fieldType */
        $fieldType = new $fieldTypeClass();

        /** @var \SuperV\Platform\Domains\Resource\Field\Field $field */
        $field = new Field($fieldType, $this->params);

        if (! $field->hasFlag('nullable')) {
            $field->addFlag('required');
        }

        return $field;
    }
}