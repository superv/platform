<?php

namespace Tests\Platform\Domains\TaskManager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\TaskManager\TaskModel;
use SuperV\Platform\Testing\PlatformTestCase;
use Tests\Platform\Domains\TaskManager\Fixtures\TestHandler;

class TestCase extends PlatformTestCase
{
    use RefreshDatabase;

    protected $installs = ['superv.modules.task_manager'];

    protected function makeTaskModel(array $taskData = null): TaskModel
    {
        return sv_resource('tasks')->create($taskData ?? $this->makeTaskData());
    }

    protected function makeTaskData(array $overrides = []): array
    {
        return array_merge(
            ['title'   => 'Run Recipe',
             'status'  => 'pending',
             'handler' => TestHandler::class,
             'payload' => $this->makeTaskPayload()], $overrides
        );
    }

    /**
     * @return array
     */
    protected function makeTaskPayload(): array
    {
        return ['server_id' => 3,
                'recipe_id' => 5,
        ];
    }
}
