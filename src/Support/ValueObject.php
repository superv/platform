<?php

namespace SuperV\Platform\Support;

use ReflectionClass;

abstract class ValueObject
{
    /**
     * @var string
     */
    protected $value;

    public function __construct(string $value)
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

    public static function all()
    {
        $reflection = new ReflectionClass(static::class);
        $vars = $reflection->getConstants();

        return collect($vars)
            ->map(function ($value) {
                return [$value, str_unslug($value)];
            })
            ->toAssoc()
            ->all();
    }
}