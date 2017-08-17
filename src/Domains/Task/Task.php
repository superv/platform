<?php namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Task\Model\TaskModel;

class Task
{
    /**
     * @var TaskModel
     */
    private $model;

    /** @var  TaskResult */
    private $result;

    private $commands;

    public function __construct(TaskModel $model
    ) {
        $this->model = $model;

        $this->commands = array_get($model->payload, 'commands', []);
    }

    public function appendOutput($buffer)
    {
        $this->model->appendOutput($buffer);
    }

    public function server()
    {
        return $this->model->getServer();
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function completed()
    {
        $this->model->update([
            'status'   => TaskModel::COMPLETED,
            'info' => $this->result->getMessage(),
        ]);

        return $this;
    }

    public function started()
    {
        $this->model->update([
            'status' => TaskModel::RUNNING,
        ]);

        return $this;
    }

    public function failed()
    {
        $this->model->update([
            'status'   => TaskModel::FAILED,
            'info' => $this->result->getMessage(),
        ]);

        return $this;
    }

    public function setResult(TaskResult $result)
    {

        $this->result = $result;

        if ($result->success()) {
            $this->completed();
        } else {
            $this->failed();
        }
    }
}