<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

interface HookHandlerInterface
{
    public function hook(string $identifier, string $hookHandler, string $subKey = null);
}
