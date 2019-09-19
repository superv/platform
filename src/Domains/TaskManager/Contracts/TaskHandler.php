<?php

namespace SuperV\Platform\Domains\TaskManager\Contracts;

interface TaskHandler
{
    public function handle(array $payload = []);
}
