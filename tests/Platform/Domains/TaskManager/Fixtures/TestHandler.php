<?php

namespace Tests\Platform\Domains\TaskManager\Fixtures;

use SuperV\Platform\Domains\TaskManager\Contracts\TaskHandler;

class TestHandler implements TaskHandler
{
    public function handle(array $payload = [])
    {
    }
}
