<?php

namespace Tests\Platform\Domains\TaskManager;

use SuperV\Platform\Domains\TaskManager\Contracts\TaskHandler;
use Tests\Platform\Domains\TaskManager\Fixtures\TestHandler;

class Task extends TaskManager
{
    function test__create()
    {
        $taskData = $this->makeTaskData();
        $task = $this->makeTaskModel($taskData);

        $this->assertNotNull($task);
        $this->assertEquals($taskData, $task->only('title', 'handler', 'payload'));
        $this->assertInstanceOf(Task::class, $task);

        $this->assertEquals(TestHandler::class, $task->getHandlerClass());
        $this->assertInstanceOf(TaskHandler::class, $task->getHandler());
        $this->assertEquals($taskData['payload'], $task->getPayload());
    }
}
