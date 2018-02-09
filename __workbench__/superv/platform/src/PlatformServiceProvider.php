<?php

namespace SuperV\Platform;

use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Exceptions\CorePlatformException;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\Listeners\PortDetectedListener;
use SuperV\Platform\Listeners\RouteMatchedListener;
use SuperV\Platform\Packs\Auth\PlatformUsers;
use SuperV\Platform\Packs\Auth\User;
use SuperV\Platform\Packs\Auth\Users;
use SuperV\Platform\Packs\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Packs\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Packs\Port\PortDetectedEvent;
use SuperV\Platform\Providers\BaseServiceProvider;
use SuperV\Platform\Providers\ThemeServiceProvider;
use SuperV\Platform\Providers\TwigServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
        MigrationServiceProvider::class,
        ThemeServiceProvider::class,
    ];

    protected $bindings = [];

    protected $singletons = [
        Users::class => PlatformUsers::class,
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
        /**
         * Migration Scopes
         */
        config(['superv.migrations.scopes' => [
            'platform' => Platform::path('database/migrations'),
        ]]);

        /**
         * Register User Model
         */
        $this->bindings[User::class] = Platform::config('auth.user.model');

        if (Platform::config('twig.enabled')) {
            $this->providers[] = TwigServiceProvider::class;
        }

        $this->registerBindings($this->bindings);
        $this->registerSingletons($this->singletons);
        $this->registerAliases($this->aliases);
        $this->registerListeners($this->listeners);
        $this->registerCommands($this->commands);

        $this->registerListeners([
            'platform.registered' => function () {
                $this->registerProviders($this->providers);
            },
        ]);

        event('platform.registered');
    }

    public function boot()
    {
        if (config('superv.installed') === true) {
            Platform::boot();
        }
    }
}