<?php

namespace SuperV\Platform\Domains\Droplet\Events;

use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Events\BaseEvent;

class MakingDropletEvent extends BaseEvent
{
    /**
     * @var \SuperV\Platform\Domains\Droplet\DropletModel
     */
    public $droplet;

    public function __construct(DropletModel $droplet)
    {
        $this->droplet = $droplet;
    }
}