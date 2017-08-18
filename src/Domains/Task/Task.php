<?php namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Task\Model\TaskModel;

class Task
{
    const COMPLETED = 0;
    const PENDING = 1;
    const RUNNING = 2;
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
                'title' => $job->getTitle(),
                'status' => self::PENDING
            ]
        );

        return (new Task())->setModel($model)->watch($job);
    }

    public function watch(Job $job)
    {
        $job->setListener($this->newListener());
        $job->setTask($this);

        return $this;
    }

    public function createJobModel(Job $job)
    {
        $jobModel = $this->model->jobs()->create(
            [
                'title' => $job->getTitle(),
                'status' => self::PENDING
            ]
        );

        $job->setModel($jobModel);

        return $this;
    }

    public function newListener()
    {
        return new TaskListener($this);
    }

    public function appendOutput($buffer)
    {
        $this->model->appendOutput($buffer);
    }

    public function payload($key = null, $default = null)
    {
        if ($key) {
            return array_get($this->model->payload, $key, $default);
        }

        return $this->model->payload;
    }

    public function completed()
    {
        $this->model->update([
            'status' => self::COMPLETED,
        ]);

        \Log::info('TASK COMPLETED');

        return $this;
    }

    public function started()
    {
        \Log::info('TASK STARTED: ' . $this->model->getTitle());

        $this->model->update([
            'status' => self::RUNNING,
        ]);

        return $this;
    }

    public function failed($message)
    {
        $this->model->update([
            'status' => self::FAILED,
            'info'   => $message,
        ]);

        \Log::error('TASK FAILED: '.$message);

        return $this;
    }


}