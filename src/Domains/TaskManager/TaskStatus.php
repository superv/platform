<?php

namespace SuperV\Platform\Domains\TaskManager;

use SuperV\Platform\Support\ValueObject;

class TaskStatus extends ValueObject
{
    private const ERROR = 'error';
    private const SUCCESS = 'success';
    private const PROCESSING = 'processing';
    private const PENDING = 'pending';

    public function isPending(): bool
    {
        return $this->equals(static::pending());
    }

    public function isError(): bool
    {
        return $this->equals(static::error());
    }

    public function isSuccess(): bool
    {
        return $this->equals(static::success());
    }

    public function isProcessing(): bool
    {
        return $this->equals(static::processing());
    }

    public static function pending(): self
    {
        return new static(self::PENDING);
    }

    public static function error(): self
    {
        return new static(self::ERROR);
    }

    public static function success(): self
    {
        return new static(self::SUCCESS);
    }

    public static function processing(): self
    {
        return new static(self::PROCESSING);
    }
}
