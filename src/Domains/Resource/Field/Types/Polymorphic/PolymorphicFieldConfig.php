<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Polymorphic;

use Closure;
use SuperV\Platform\Domains\Resource\Field\FieldConfig;

class PolymorphicFieldConfig extends FieldConfig
{
    protected $types = [];

    public function add($typeName, Closure $callback)
    {
        $this->types[$typeName] = $callback;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public static function make()
    {
        return new static;
    }
}
