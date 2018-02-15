<?php

namespace SuperV\Platform\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Droplet\DropletModel;

class ThemeActivatedEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Droplet\DropletModel
     */
    public $theme;

    public function __construct(DropletModel $theme)
    {
        $this->theme = $theme;
    }
}