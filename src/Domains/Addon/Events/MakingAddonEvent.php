<?php

namespace SuperV\Platform\Domains\Addon\Events;

use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Events\BaseEvent;

class MakingAddonEvent extends BaseEvent
{
    /**
     * @var \SuperV\Platform\Domains\Addon\AddonModel
     */
    public $addon;

    public function __construct(AddonModel $addon)
    {
        $this->addon = $addon;
    }
}