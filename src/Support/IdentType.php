<?php

namespace SuperV\Platform\Support;

class IdentType extends ValueObject
{
    private const FORM = 'forms';
    private const FIELD = 'fields';
    private const ENTRY = 'entries';
    private const RESOURCE = 'resources';

    protected $handle;

    public function __construct(string $type, $handle = null)
    {
        parent::__construct($type);

        if ($handle) {
            $this->handle = $handle;
        }
    }

    public function isResource(): bool
    {
        return $this->equals(static::resource());
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

    public function handle()
    {
        return $this->handle;
    }

    public static function resource(): self
    {
        return new static(self::RESOURCE);
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
