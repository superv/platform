<?php

namespace SuperV\Platform;

use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Authorization\Haydar;
use SuperV\Platform\Domains\Authorization\HaydarBouncer;
use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletMakeMigrationCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletRunMigrationCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Navigation\Collector;
use SuperV\Platform\Domains\Navigation\DropletNavigationCollector;
use SuperV\Platform\Domains\Routing\Router;
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

    protected $_bindings = [
        Collector::class => DropletNavigationCollector::class,
        Haydar::class    => HaydarBouncer::class,
    ];

    protected $aliases = [
        'Feature' => 'SuperV\Platform\Domains\Feature\FeatureFacade',
        'Current' => 'SuperV\Platform\Facades\CurrentFacade',

    ];

    protected $_singletons = [
        'SuperV\Platform\Domains\Auth\Contracts\Users' => 'SuperV\Platform\Domains\Auth\Users',
        'droplets'                                     => DropletCollection::class,
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched'         => 'SuperV\Platform\Listeners\RouteMatchedListener',
        'SuperV\Platform\Domains\Port\PortDetectedEvent' => 'SuperV\Platform\Listeners\PortDetectedListener',
    ];

    protected $commands = [
        SuperVInstallCommand::class,
        DropletInstallCommand::class,
        MakeDropletCommand::class,
        DropletMakeMigrationCommand::class,
        DropletRunMigrationCommand::class,
    ];

    public function register()
    {
        if ($this->app->runningInConsole()) {
            MigrationScopes::register('platform', __DIR__.'/../database/migrations');
        }
        $this->mergeConfigFrom(
            __DIR__.'/../config/superv.php', 'superv'
        );

        /**
         * Register User Model
         */
        $this->_bindings[User::class] = sv_config('auth.user.model');

        if (sv_config('twig.enabled')) {
            $this->providers[] = TwigServiceProvider::class;
        }

        $this->registerBindings($this->_bindings);
        $this->registerSingletons($this->_singletons);
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

        superv('droplets')->put('superv.platform', Platform::instance());

        Platform::boot();

        app(Router::class)->loadFromPath(Platform::path('routes'));
    }
}