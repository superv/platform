<?php

namespace SuperV\Platform\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SuperV\Platform\Domains\Addon\AddonModel;

class ThemeActivatedEvent
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Addon\AddonModel
     */
    public $theme;

    public function __construct(AddonModel $theme)
    {
        $this->theme = $theme;
    }
}