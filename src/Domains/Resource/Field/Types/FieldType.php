<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

abstract class FieldType
{
    public static function resolve($type)
    {
        $class = static::resolveClass($type);

        return new $class;
    }

    public static function resolveClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType $class */
        $class = $base."\\".studly_case($type);

        if (! class_exists($class)) {
            $class = $base."\\".studly_case($type.'_field');
        }

        return $class;
    }
}