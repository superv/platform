<?php

namespace SuperV\Platform\Domains\Resource\Field;

class FieldValue
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Field
     */
    protected $field;

    protected $value;

    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    public function set($value)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function fieldName()
    {
        return $this->field->getName();
    }
}