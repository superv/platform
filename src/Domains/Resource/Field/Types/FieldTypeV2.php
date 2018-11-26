<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

abstract class FieldTypeV2
{
    protected $columnName;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field
     */
    protected $field;

    public function __construct(?Field $field = null)
    {
        $this->field = $field;
    }

    public function getColumnName(): ?string
    {
        return $this->columnName;
    }

    public function getConfigValue($key, $default = null)
    {
        return $this->field->getConfigValue($key, $default);
    }

    public function getConfig()
    {
        return $this->field->getConfig();
    }

    public function getName()
    {
        return $this->field->getName();
    }

    public function getType()
    {
        return $this->field->getType();
    }

    public static function resolve($type)
    {
        $class = static::resolveClass($type);

        return new $class;
    }

    public static function resolveClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types\V2';

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType $class */
        $class = $base."\\".studly_case($type);

        return $class;
    }
}