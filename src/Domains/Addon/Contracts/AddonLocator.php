<?php

namespace SuperV\Platform\Domains\Addon\Contracts;

interface AddonLocator
{
    public function locate(string $identifier, string $type);
}
