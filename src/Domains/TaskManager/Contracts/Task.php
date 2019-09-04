<?php

namespace SuperV\Platform\Domains\TaskManager\Contracts;

interface Task
{
    public function getHandlerClass(): string;

    public function getHandler(): TaskHandler;

    public function getPayload(): array;
}
