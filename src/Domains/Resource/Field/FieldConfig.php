<?php

namespace SuperV\Platform\Domains\Resource\Field;

use ReflectionClass;
use ReflectionProperty;
use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Support\Concerns\Hydratable;

class FieldConfig implements Arrayable
{
    use Hydratable;

    /**
     * Self resource handle
     *
     * @var string
     */
    protected $self;

    public function __construct(array $attributes = [])
    {
        if (! empty($attributes)) {
            $this->hydrate($attributes);
        }
    }

    /**
     * @return string
     */
    public function getSelf(): string
    {
        return $this->self;
    }

    public function setSelf(string $self): FieldConfig
    {
        $this->self = $self;

        return $this;
    }

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
