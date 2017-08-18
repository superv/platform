<?php namespace SuperV\Platform\Domains\Task;

class TaskListener
{
    /**
     * @var Task
     */
    private $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function listen($type, $buffer)
    {
      \Log::info("TASK LISTENER: {$type}: {$buffer}");

        $this->task->appendOutput($buffer);
    }

    public function callable()
    {
        return [$this, 'listen'];
    }
}