<?php

namespace SuperV\Platform\Domains\TaskManager;

use SuperV\Platform\Domains\TaskManager\Contracts\Task;

class TaskBuilder
{
    /** @var \SuperV\Platform\Domains\TaskManager\Contracts\Task */
    protected $task;

    protected $handler;

    /** @var array */
    protected $payload;

    protected $title;

    public function build(): TaskBuilder
    {
        $this->task = TaskModel::create([
            'title'   => $this->title,
            'status'  => 'pending',
            'handler' => $this->handler,
            'payload' => $this->payload,
        ]);

        return $this;
    }

    public function handler($handler): TaskBuilder
    {
        $this->handler = $handler;

        return $this;
    }

    public function payload(array $payload): TaskBuilder
    {
        $this->payload = $payload;

        return $this;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function title($title): TaskBuilder
    {
        $this->title = $title;

        return $this;
    }

    public static function make(): TaskBuilder
    {
        return app(static::class);
    }
}
