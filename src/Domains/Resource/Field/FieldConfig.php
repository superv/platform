<?php

namespace SuperV\Platform\Domains\Resource\Field;

use ReflectionClass;
use ReflectionProperty;
use SuperV\Platform\Contracts\Arrayable;

class FieldConfig implements Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return collect((new ReflectionClass(static::class))->getProperties())
            ->map(function (ReflectionProperty $property) {
                $value = $this->{$property->getName()};
                if (is_object($value)) {
                    $value = (string)$value;
                }

                return [snake_case($property->getName()), $value];
            })->toAssoc()->all();
    }
}
