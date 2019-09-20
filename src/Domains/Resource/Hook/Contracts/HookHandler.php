<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

interface HookHandler
{
    public function hook(string $identifier, string $hookHandler, string $subKey = null);
}
