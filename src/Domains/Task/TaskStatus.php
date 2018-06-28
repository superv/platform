<?php

namespace SuperV\Platform\Domains\Task;

class TaskStatus
{
    private const PENDING = 'pending';
    private const ASSIGNED = 'assigned';
    private const IN_PROGRESS = 'in_progress';
    private const COMPLETED = 'completed';

    private $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function isPending()
    {
        return $this->equals(static::pending());
    }

    public function isAssigned()
    {
        return $this->equals(static::assigned());
    }

    public function isInProgress()
    {
        return $this->equals(static::inProgress());
    }

    public function isCompleted()
    {
        return $this->equals(static::completed());
    }

    private function equals(self $another)
    {
        return $this->status === $another->status;
    }

    public static function pending()
    {
        return new static(self::PENDING);
    }

    public static function assigned()
    {
        return new static(self::ASSIGNED);
    }

    public static function inProgress()
    {
        return new static(self::IN_PROGRESS);
    }

    public static function completed()
    {
        return new static(self::COMPLETED);
    }

    public static function all()
    {
        return [
            static::PENDING     => 'Pending',
            static::ASSIGNED    => 'Assigned',
            static::IN_PROGRESS => 'In Progress',
            static::COMPLETED   => 'Completed',
        ];
    }

    public function __toString()
    {
        return $this->status;
    }
}