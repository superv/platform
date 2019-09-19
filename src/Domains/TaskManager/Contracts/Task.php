<?php

namespace SuperV\Platform\Domains\TaskManager\Contracts;

use SuperV\Platform\Domains\TaskManager\TaskStatus;

interface Task
{
    public function setStatus(TaskStatus $status): void;

    public function status(): TaskStatus;

    public function getInfo(): ?string;

    public function setInfo(string $info): void;

    public function getHandlerClass(): string;

    public function getHandler(): TaskHandler;

    public function getPayload(): array;
}
