<?php

namespace Tests\Platform\Domains\TaskManager;

class TaskManagerTest extends TestCase
{
    function test__module_is_installed()
    {
        $this->assertNotNull(superv('addons')->get('superv.modules.task_manager'));
    }
}
