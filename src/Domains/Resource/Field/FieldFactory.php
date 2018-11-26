<?php

namespace SuperV\Platform\Domains\Resource\Field;

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

    protected function create(): Field
    {
        $field = new Field($this->params);
        $field->bindFieldType();

        return $field;
    }

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
}