<?php

namespace SuperV\Platform\Domains\Addon\Types\Panel;

use SuperV\Platform\Domains\Addon\AddonServiceProvider;

class PanelServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        parent::register();

        $this->publishAssets();
    }

    protected function publishAssets()
    {
        $vendor = $this->addon->getVendor();
        $name = $this->addon->getName();

        $this->publishes(
            [$this->addon->resourcePath('assets') => public_path(sprintf("vendor/%s/%s", $vendor, $name))],
            sprintf("%s.assets", $name)
        );
    }
}