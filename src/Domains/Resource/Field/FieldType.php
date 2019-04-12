<?php

namespace SuperV\Platform\Domains\Resource\Field;

class FieldType
{
    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field */
    protected $field;

    public function __construct()
    {
    }

    public function __toString()
    {
      return  $this->field->getType();
    }

    protected function boot() {}
    /**
     * @param \SuperV\Platform\Domains\Resource\Field\Contracts\Field $field
     */
    public function setField(\SuperV\Platform\Domains\Resource\Field\Contracts\Field $field): void
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

    public static function resolveType($type)
    {
        $class = static::resolveTypeClass($type);

        return new $class;
    }

    public static function resolveTypeClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        $class = $base."\\".studly_case($type);

        if (! class_exists($class)) {
            $class = $base."\\".studly_case($type.'_field');
        }

        return $class;
    }
}