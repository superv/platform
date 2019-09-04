<?php

namespace Tests\Platform\Domains\TaskManager;

use Queue;
use SuperV\Platform\Domains\TaskManager\Deployer;
use SuperV\Platform\Domains\TaskManager\DeployTaskJob;

class DeployTaskJobTest extends TestCase
{
    function test__queue()
    {
        $task = $this->makeTaskModel();

        Queue::fake();

        DeployTaskJob::dispatch($task);

        Queue::assertPushed(DeployTaskJob::class, function (DeployTaskJob $job) use ($task) {
            return $job->getTask() === $task;
        });
    }

    function test__deploy()
    {
        $task = $this->makeTaskModel();

        $deployerMock = $this->bindMock(Deployer::class);

        $deployerMock->shouldReceive('deploy')->with($task)->once();

        dispatch_now(new DeployTaskJob($task));
    }
}
