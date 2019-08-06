<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\Field as Field;

class FieldType
{
    protected $type;

    /** @var Field */
    protected $field;

    protected function boot() { }

    public function __toString()
    {
        return $this->type ?? $this->field->getType();
    }

    public function setField(Field $field): void
    {
        $this->field = $field;

        $this->boot();
    }

    public function addFlag($flag)
    {
        $this->field->addFlag($flag);
    }

    public function getName()
    {
        return $this->field->getName();
    }

    public function getConfigValue($key, $default = null)
    {
        return $this->field->getConfigValue($key, $default);
    }

    public function setConfig(array $config)
    {
        return $this->field->setConfig($config);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public static function resolveType($type)
    {
        $class = static::resolveTypeClass($type);

        return new $class;
    }

    public static function resolveTypeClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        $class = $base."\\".studly_case($type.'_field');

        // custom directory
        if (! class_exists($class)) {
            $class = $base."\\".studly_case($type)."\\".studly_case($type.'_field');
        }

        return $class;
    }
}
