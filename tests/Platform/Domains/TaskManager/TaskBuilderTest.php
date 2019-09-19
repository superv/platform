<?php

namespace Tests\Platform\Domains\TaskManager;

use SuperV\Platform\Domains\TaskManager\Contracts\Task;
use SuperV\Platform\Domains\TaskManager\TaskBuilder;
use Tests\Platform\Domains\TaskManager\Fixtures\TestHandler;

class TaskBuilderTest extends TestCase
{
    function test__build()
    {
        $builder = $this->makeTaskBuilder();

        $builder->build();

        $task = $builder->getTask();
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($this->makeTaskPayload(), $task->getPayload());
    }

    protected function makeTaskBuilder(): TaskBuilder
    {
        return TaskBuilder::make()
                          ->handler(TestHandler::class)
                          ->title('Test Task')
                          ->payload($this->makeTaskPayload());
    }
}
