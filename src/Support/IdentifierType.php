<?php

namespace SuperV\Platform\Support;

class IdentifierType extends ValueObject
{
    private const FORM = 'form';
    private const FIELD = 'field';
    private const ENTRY = 'entry';

    protected $id;

    public function __construct(string $value, $id = null)
    {
        parent::__construct($value);

        if ($id) {
            $this->id = $id;
        }
    }

    public function isForm(): bool
    {
        return $this->equals(static::form());
    }

    public function isField(): bool
    {
        return $this->equals(static::field());
    }

    public function isEntry(): bool
    {
        return $this->equals(static::entry());
    }

    public function id()
    {
        return $this->id;
    }

    public static function form(): self
    {
        return new static(self::FORM);
    }

    public static function field(): self
    {
        return new static(self::FIELD);
    }

    public static function entry(): self
    {
        return new static(self::ENTRY);
    }
}
