<?php

namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Task\Event\TaskOutputEvent;
use SuperV\Platform\Domains\Task\Event\TaskStatusUpdatedEvent;
use SuperV\Platform\Domains\Task\Model\TaskModel;

class Task
{
    const PENDING = 0;
    const RUNNING = 1;
    const COMPLETED = 2;
    const FAILED = 3;
    const COMPLETED_WITH_ERRORS = 4;
    const ABORTING = 5;
    const ABORTED = 6;

    /**
     * @var TaskModel
     */
    private $model;

    public function setModel(TaskModel $model)
    {
        $this->model = $model;

        return $this;
    }

    public function createSubTask(Job $job)
    {
        $model = $this->model->subtasks()->create(
            [
                'payload' => ['job' => serialize($job)],
                'title'   => $job->getTitle(),
                'status'  => self::PENDING,
            ]
        );

        return (new self())->setModel($model)->watch($job);
    }

    public function watch(Job $job)
    {
        $job->setListener($this->newListener());
        $job->setTask($this);

        return $this;
    }

    public function newListener()
    {
        return new TaskListener($this);
    }

    public function appendOutput($buffer)
    {
        $this->model->appendOutput($buffer);

        event(new TaskOutputEvent($this->model, $buffer));
    }

    public function payload($key = null, $default = null)
    {
        if ($key) {
            return array_get($this->model->payload, $key, $default);
        }

        return $this->model->payload;
    }

    public function status($status, $message = null)
    {
        $update = [
            'status' => $status,
        ];
        array_set_if_not(is_null($message), $update, 'message', $message);

        $this->model->update($update);

        event(new TaskStatusUpdatedEvent($this->model));

        return $this;
    }

    public function completed()
    {
        return $this->status(self::COMPLETED);
    }

    public function started()
    {
        return $this->status(self::RUNNING);
    }

    public function failed($message)
    {
        return $this->status(self::FAILED, $message);
    }
}
