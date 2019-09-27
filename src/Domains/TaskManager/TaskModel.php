<?php

namespace SuperV\Platform\Domains\TaskManager;

use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\TaskManager\Contracts\Task;
use SuperV\Platform\Domains\TaskManager\Contracts\TaskHandler;

class TaskModel extends ResourceEntry implements Task
{
    protected $table = 'sv_tasks';

    protected $casts = ['payload' => 'json'];

    protected $attributes = ['status' => 'pending'];

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

    public function status(): TaskStatus
    {
        return new TaskStatus($this->status);
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setStatus(TaskStatus $status): void
    {
        $this->update(['status' => $status]);
    }

    public function setInfo(string $info): void
    {
        $this->update(['info' => $info]);
    }
}
