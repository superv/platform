<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Support\ValueObject;

class FieldTypes extends ValueObject
{
    private const TEXT = TextField::class;

    public function isText(): bool
    {
        return $this->equals(static::text());
    }

    public static function text(): self
    {
        return new static(self::TEXT);
    }
}