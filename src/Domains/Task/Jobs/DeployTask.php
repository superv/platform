<?php

namespace SuperV\Platform\Domains\Task\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Queue;
use SuperV\Platform\Domains\Task\Task;
use SuperV\Platform\Domains\Task\Deployer;

class DeployTask implements ShouldQueue
{
    use InteractsWithQueue;

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

    public function queue(Queue $queue, $command)
      {
          $queue->pushOn('superv-high', $command);
      }
}
