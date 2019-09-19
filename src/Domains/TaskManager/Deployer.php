<?php

namespace SuperV\Platform\Domains\TaskManager;

use Exception;
use SuperV\Platform\Domains\TaskManager\Contracts\Task;

class Deployer
{
    public function deploy(Task $task)
    {
        $task->setStatus(TaskStatus::processing());

        $handler = $task->getHandler();

        try {
            $handler->handle($task->getPayload());

            $task->setStatus(TaskStatus::success());
        } catch (Exception $e) {
            $task->setStatus(TaskStatus::error());
            $task->setInfo($e->getMessage());
        }
    }

    public static function resolve()
    {
        return app(static::class);
    }
}
