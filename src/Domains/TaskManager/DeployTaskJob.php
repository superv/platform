<?php

namespace SuperV\Platform\Domains\TaskManager;

use Illuminate\Contracts\Queue\ShouldQueue;
use SuperV\Platform\Domains\TaskManager\Contracts\Task;
use SuperV\Platform\Support\Dispatchable;

class DeployTaskJob implements ShouldQueue
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\TaskManager\Contracts\Task
     */
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle(Deployer $deployer)
    {
        $deployer->deploy($this->task);
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
