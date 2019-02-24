<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Support\Dispatchable;

class SeedAddon
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Addon\Addon
     */
    protected $addon;

    public function __construct(Addon $addon)
    {
        $this->addon = $addon;
    }

    public function handle()
    {
        $seederClass = $this->addon->seederClass();

        if (class_exists($seederClass)) {
            app()->make($seederClass, ['addon' => $this->addon])->seed();
        }
    }
}