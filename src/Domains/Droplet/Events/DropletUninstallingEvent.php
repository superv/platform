<?php

namespace SuperV\Platform\Domains\Droplet\Events;

use SuperV\Platform\Domains\Droplet\Droplet;

class DropletUninstallingEvent
{
    /**
     * @var Droplet
     */
    public $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }
}