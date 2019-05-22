<?php

namespace SuperV\Platform\Support;

use InvalidArgumentException;
use ReflectionClass;

abstract class ValueObject
{
    /**
     * @var string
     */
    protected $value;

    public function __construct(string $value)
    {
        if (! in_array($value, static::all())) {
            throw new InvalidArgumentException("Invalid value: [{$value}]");
        }

        $this->setValue($value);
    }

    public function setValue(string $value)
    {
        $this->value = $value;
    }

    protected function equals(self $another)
    {
        return $this->value === $another->value;
    }

    public function __toString()
    {
        return $this->value;
    }

    public static function choices()
    {
        return collect(static::all())
            ->map(function ($value) {
                return [$value, str_unslug($value)];
            })
            ->toAssoc()
            ->all();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected static function all(): array
    {
        $reflection = new ReflectionClass(static::class);
        $vars = $reflection->getConstants();

        return $vars;
    }
}