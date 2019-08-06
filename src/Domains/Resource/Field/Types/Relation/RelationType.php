<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Relation;

use SuperV\Platform\Support\ValueObject;

class RelationType extends ValueObject
{
    private const ONE_TO_MANY = 'one_to_many';
    private const MANY_TO_MANY = 'many_to_many';
    private const ONE_TO_ONE = 'one_to_one';

    public function isOneToMany(): bool
    {
        return $this->equals(static::oneToMany());
    }

    public function isManyToMany(): bool
    {
        return $this->equals(static::manyToMany());
    }

    public function isOneToOne(): bool
    {
        return $this->equals(static::oneToOne());
    }

    public static function oneToMany(): self
    {
        return new static(self::ONE_TO_MANY);
    }

    public static function oneToOne(): self
    {
        return new static(self::ONE_TO_ONE);
    }

    public static function manyToMany(): self
    {
        return new static(self::MANY_TO_MANY);
    }
}
