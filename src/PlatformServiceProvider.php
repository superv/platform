<?php

namespace SuperV\Platform;

use Auth;
use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Domains\Auth\PlatformUserProvider;
use SuperV\Platform\Domains\Auth\PlatformUsers;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Auth\Users;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Listeners\PortDetectedListener;
use SuperV\Platform\Listeners\RouteMatchedListener;
use SuperV\Platform\Providers\BaseServiceProvider;
use SuperV\Platform\Providers\TwigServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
        'SuperV\Platform\Providers\ThemeServiceProvider',
        'SuperV\Platform\Adapters\AdapterServiceProvider',
        'SuperV\Platform\Domains\Asset\AssetServiceProvider',
        'SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider',
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

        Auth::provider('platform', function ($app) {
            return new PlatformUserProvider($app['hash'], config('superv.auth.user.model'));
        });

        config()->set('auth.defaults.guard', 'platform');

        config()->set('auth.guards.platform', [
            'driver'   => 'session',
            'provider' => 'platform',
        ]);

        config()->set('auth.providers.platform', [
            'driver' => 'platform',
        ]);
    }
}