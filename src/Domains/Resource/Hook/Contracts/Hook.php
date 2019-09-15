<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

interface Hook
{
    public function hook(string $identifier, string $hookHandler, string $subKey = null);
}
