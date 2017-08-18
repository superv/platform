<?php namespace SuperV\Platform\Domains\Task\Jobs;

use SuperV\Platform\Domains\Task\Deployer;
use SuperV\Platform\Domains\Task\Task;

class DeployTaskJob
{
    /**
     * @var Task
     */
    private $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }
    public function handle(Deployer $deployer)
    {
        $deployer->task($this->task)->deploy();
    }
}