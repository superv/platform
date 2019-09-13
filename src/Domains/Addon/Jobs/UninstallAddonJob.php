<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use Illuminate\Support\Facades\Artisan;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Dispatchable;

class UninstallAddonJob
{
    use Dispatchable;
    /**
     * @var string|\SuperV\Platform\Domains\Addon\Addon
     */
    protected $addon;

    public function __construct($addon)
    {
        $this->addon = $addon;
    }

    public function handle(AddonCollection $addons)
    {
        if (is_string($this->addon)) {
            $this->addon = $addons->get($this->addon);
        }
        if (! $this->addon) {
            PlatformException::fail("Addon not found: ".$this->addon);
        }

        AddonUninstallingEvent::dispatch($this->addon);

        Artisan::call('migrate:reset', ['--namespace' => $this->addon->getIdentifier(), '--force' => true]);

        $this->addon->entry()->delete();

        return true;
    }
}
