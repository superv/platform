<?php

namespace SuperV\Platform\Domains\Addon\Contracts;

interface DropletLocator
{
    public function locate(string $slug);
}