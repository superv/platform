<?php

namespace Tests\Platform\Domains\TaskManager;

use SuperV\Platform\Domains\TaskManager\Deployer;
use Tests\Platform\Domains\TaskManager\Fixtures\TestHandler;

class DeployerTest extends TestCase
{
    function test__deploy()
    {
        $taskData = $this->makeTaskData();
        $handlerMock = $this->bindMock(TestHandler::class);
        $handlerMock->shouldReceive('handle')->with($taskData['payload'])->once();

        /** @var Deployer $deployer */
        $deployer = app(Deployer::class);

        $deployer->deploy($this->makeTaskModel($taskData));
    }
}
