<?php namespace SuperV\Platform\Domains\Task;

class Job
{
    /**
     * @var Task
     */
    protected $task;

    protected $title;

    protected $listener;

    /**
     * @param Task $task
     *
     * @return Job
     */
    public function setTask(Task $task): Job
    {
        $this->task = $task;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getListener()
    {
        return $this->listener;
    }

    /**
     * @param mixed $listener
     *
     * @return Job
     */
    public function setListener($listener)
    {
        $this->listener = $listener;

        return $this;
    }

    /**
     * @return Task
     */
    public function getTask(): Task
    {
        return $this->task;
    }
}