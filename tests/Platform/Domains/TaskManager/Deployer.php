<?php

namespace Tests\Platform\Domains\TaskManager;

use Exception;
use Tests\Platform\Domains\TaskManager\Fixtures\TestHandler;

class Deployer extends TaskManager
{
    function test__deploy()
    {
        $taskData = $this->makeTaskData();
        $task = $this->makeTaskModel($taskData);
        $this->assertTrue($task->status()->isPending());

        $handlerMock = $this->bindMock(TestHandler::class);
        $handlerMock->shouldReceive('handle')->with($taskData['payload'])->once();

        Deployer::resolve()->deploy($task);

        $this->assertTrue($task->status()->isSuccess());
    }

    function test__deploy_fail()
    {
        $taskData = $this->makeTaskData();
        $handlerMock = $this->bindMock(TestHandler::class);
        $handlerMock->shouldReceive('handle')->with($taskData['payload'])->once()->andThrowExceptions([new Exception('Deploy failed!')]);

        $task = $this->makeTaskModel($taskData);
        Deployer::resolve()->deploy($task);

        $this->assertTrue($task->status()->isError());

        $this->assertEquals('Deploy failed!', $task->getInfo());
    }
}
