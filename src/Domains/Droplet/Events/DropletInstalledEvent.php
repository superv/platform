<?php

namespace SuperV\Platform\Domains\Droplet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Droplet\Droplet;

class DropletInstalledEvent
{
    use Dispatchable;

    /** @var \SuperV\Platform\Domains\Droplet\Droplet */
    public $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }
}