<?php

namespace SuperV\Platform;

use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Exceptions\CorePlatformException;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\Listeners\PortDetectedListener;
use SuperV\Platform\Listeners\RouteMatchedListener;
use SuperV\Platform\Packs\Auth\PlatformUser;
use SuperV\Platform\Packs\Auth\User;
use SuperV\Platform\Packs\Auth\Users;
use SuperV\Platform\Packs\Auth\PlatformUsers;
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

    protected $bindings = [];

    protected $singletons = [
        Users::class => PlatformUsers::class
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched' => RouteMatchedListener::class,
        PortDetectedEvent::class                 => PortDetectedListener::class,
    ];

    protected $commands = [
        SuperVInstallCommand::class,
        DropletInstallCommand::class,
    ];

    protected $aliases = [
        'Platform' => PlatformFacade::class,
    ];

    public function register()
    {
        parent::register();

        config(['superv.migrations.scopes' => [
            'platform' => Platform::path('database/migrations'),
        ]]);

        $this->registerBindings([User::class => Platform::config('auth.user.model')]);
    }

    public function boot()
    {
        if (config('superv.installed') === true) {
            Platform::boot();
        }
    }
}