<?php

namespace SuperV\Platform\Domains\Droplet\Contracts;

interface DropletLocator
{
    public function locate(string $slug);
}