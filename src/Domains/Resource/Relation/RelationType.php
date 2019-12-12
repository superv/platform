<?php

namespace SuperV\Platform\Domains\Resource\Relation;

use SuperV\Platform\Support\ValueObject;

class RelationType extends ValueObject
{
    private const HAS_MANY = 'has_many';
    private const HAS_ONE = 'has_one';
    private const BELONGS_TO = 'belongs_to';
    private const BELONGS_TO_MANY = 'belongs_to_many';
    private const MORPH_TO_MANY = 'morph_to_many';
    private const MORPH_ONE = 'morph_one';
    private const MORPH_TO = 'morph_to';
    private const MORPH_MANY = 'morph_many';
    private const MANY_TO_MANY = 'many_to_many';

    public function isManyToMany(): bool
    {
        return $this->equals(static::manyToMany());
    }

    public function isBelongsTo(): bool
    {
        return $this->equals(static::belongsTo());
    }

    public function isBelongsToMany(): bool
    {
        return $this->equals(static::belongsToMany());
    }

    public function isHasMany(): bool
    {
        return $this->equals(static::hasMany());
    }

    public function isHasOne(): bool
    {
        return $this->equals(static::hasOne());
    }

    public function isMorphToMany(): bool
    {
        return $this->equals(static::morphToMany());
    }

    public function isMorphOne(): bool
    {
        return $this->equals(static::morphOne());
    }

    public function isMorphTo(): bool
    {
        return $this->equals(static::morphTo());
    }

    public function isMorphMany(): bool
    {
        return $this->equals(static::morphMany());
    }

    public static function manyToMany(): self
    {
        return new static(self::MANY_TO_MANY);
    }

    public static function hasOne(): self
    {
        return new static(self::HAS_ONE);
    }

    public static function hasMany(): self
    {
        return new static(self::HAS_MANY);
    }

    public static function belongsTo(): self
    {
        return new static(self::BELONGS_TO);
    }

    public static function belongsToMany(): self
    {
        return new static(self::BELONGS_TO_MANY);
    }

    public static function morphToMany(): self
    {
        return new static(self::MORPH_TO_MANY);
    }

    public static function morphOne(): self
    {
        return new static(self::MORPH_ONE);
    }

    public static function morphTo(): self
    {
        return new static(self::MORPH_TO);
    }

    public static function morphMany(): self
    {
        return new static(self::MORPH_MANY);
    }
}