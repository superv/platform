<?php

namespace SuperV\Platform\Support;

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
}