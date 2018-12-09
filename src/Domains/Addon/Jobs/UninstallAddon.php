<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use SuperV\Platform\Exceptions\PlatformException;

class UninstallAddon
{
    /**
     * @var string
     */
    protected $addon;

    public function __construct(string $addon)
    {
        $this->addon = $addon;
    }

    public function handle(AddonCollection $addons)
    {
        if (! $addon = $addons->get($this->addon)) {
            PlatformException::fail("Addon not found: ".$this->addon);
        }

        AddonUninstallingEvent::dispatch($addon);

        $addon->entry()->delete();

        return true;
    }
}