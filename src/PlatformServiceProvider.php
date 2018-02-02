<?php

namespace SuperV\Platform;

use Platform;
use SuperV\Platform\Exceptions\CorePlatformException;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\Listeners\PortDetectedListener;
use SuperV\Platform\Listeners\RouteMatchedListener;
use SuperV\Platform\Packs\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Packs\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Packs\Port\PortDetectedEvent;
use SuperV\Platform\Providers\BaseServiceProvider;
use SuperV\Platform\Providers\ThemeServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
        MigrationServiceProvider::class,
        ThemeServiceProvider::class,
    ];

    protected $aliases = [
        'Platform' => PlatformFacade::class,
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched' => RouteMatchedListener::class,
        PortDetectedEvent::class                 => PortDetectedListener::class,
    ];

    protected $commands = [
        DropletInstallCommand::class
    ];

    public function boot()
    {
        if (config('superv.installed') === true) {
            Platform::boot();
        }
    }
}