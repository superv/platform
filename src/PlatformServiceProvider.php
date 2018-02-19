<?php

namespace SuperV\Platform;

use Auth;
use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Auth\PlatformUserProvider;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Providers\BaseServiceProvider;
use SuperV\Platform\Providers\TwigServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
        'SuperV\Platform\Providers\ThemeServiceProvider',
        'SuperV\Platform\Adapters\AdapterServiceProvider',
        'SuperV\Platform\Domains\Auth\AuthServiceProvider',
        'SuperV\Platform\Domains\Asset\AssetServiceProvider',
        'SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider',
    ];

    protected $bindings = [];

    protected $singletons = [
        'SuperV\Platform\Domains\Auth\Contracts\Users' => 'SuperV\Platform\Domains\Auth\Users',
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched'         => 'SuperV\Platform\Listeners\RouteMatchedListener',
        'SuperV\Platform\Domains\Port\PortDetectedEvent' => 'SuperV\Platform\Listeners\PortDetectedListener',
    ];

    protected $commands = [
        SuperVInstallCommand::class,
        DropletInstallCommand::class,
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
        if (config('superv.installed') !== true) {
            return;
        }

        Platform::boot();


    }
}