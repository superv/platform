<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\Types\Text\TextType;
use SuperV\Platform\Support\ValueObject;

class FieldTypes extends ValueObject
{
    private const TEXT = TextType::class;

    public function isText(): bool
    {
        return $this->equals(static::text());
    }

    public static function text(): self
    {
        return new static(self::TEXT);
    }
}