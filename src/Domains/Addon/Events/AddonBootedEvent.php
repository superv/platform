<?php

namespace SuperV\Platform\Domains\Addon\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Addon\Addon;

class AddonBootedEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Addon\Addon
     */
    public $addon;

    public function __construct(Addon $addon)
    {
        $this->addon = $addon;
    }
}