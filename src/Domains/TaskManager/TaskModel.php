<?php

namespace SuperV\Platform\Domains\TaskManager;

use Illuminate\Queue\SerializesModels;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\TaskManager\Contracts\Task;
use SuperV\Platform\Domains\TaskManager\Contracts\TaskHandler;

class TaskModel extends ResourceEntry implements Task
{
    use SerializesModels {
        SerializesModels::__sleep as parentSleep;
    }

    protected $table = 'tasks';

    protected $casts = ['payload' => 'json'];

    public $timestamps = true;

    public function getHandlerClass(): string
    {
        return $this->handler;
    }

    public function getHandler(): TaskHandler
    {
        $handler = $this->getHandlerClass();

        return app($handler);
    }

    public function getPayload(): array
    {
        return $this->payload ?? [];
    }

    public function __sleep()
    {
        $this->resource = null;

        return $this->parentSleep();
    }
}
