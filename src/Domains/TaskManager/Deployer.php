<?php

namespace SuperV\Platform\Domains\TaskManager;

use SuperV\Platform\Domains\TaskManager\Contracts\Task;

class Deployer
{
    public function deploy(Task $task)
    {
        $task->update(['status' => 'processing']);

        $handler = $task->getHandler();

        $handler->handle($task->getPayload());

        $task->update(['status' => 'done']);
    }
}
