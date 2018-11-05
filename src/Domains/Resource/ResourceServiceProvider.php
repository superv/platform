<?php

namespace SuperV\Platform\Domains\Resource;

use Platform;
use SuperV\Platform\Providers\BaseServiceProvider;

class ResourceServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        if (! Platform::isInstalled()) {
            return;
        }
    }
}