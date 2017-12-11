<?php

namespace SuperV\Platform\Domains\Droplet;

interface DropletServiceProviderInterface
{
    public function getCommands();

    public function getPath($path = null);
}