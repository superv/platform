<?php

namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Task\Model\Tasks;

class TaskBuilder
{
    protected $payload;

    protected $title;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function build()
    {
        $model = app(Tasks::class)->create(
            [
                'payload'    => $this->payload,
                'title'      => $this->title,
                'status'     => Task::PENDING,
                'created_at' => mysql_now(),
            ]);

        $this->task->setModel($model);

        return $this->task;
    }

    /**
     * @param mixed $payload
     *
     * @return TaskBuilder
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }
}
