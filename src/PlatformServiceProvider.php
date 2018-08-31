<?php

namespace SuperV\Platform;

use Bouncer;
use Platform;
use Silber\Bouncer\BouncerServiceProvider;
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
        BouncerServiceProvider::class,
    ];

    protected $_bindings = [
        Collector::class => DropletNavigationCollector::class,
        Haydar::class    => HaydarBouncer::class,
    ];

    protected $aliases = [
        'Feature' => 'SuperV\Platform\Domains\Feature\FeatureFacade',
        'Current' => 'SuperV\Platform\Facades\CurrentFacade',
        'Bouncer' => \Silber\Bouncer\BouncerFacade::class,
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
            MigrationScopes::register('platform', Platform::path('database/migrations'));
        }
        $this->mergeConfigFrom(
            __DIR__.'/../config/superv.php', 'superv'
        );

        /**
         * Register User Model
         */
        $this->_bindings[User::class] = Platform::config('auth.user.model');

        if (Platform::config('twig.enabled')) {
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

        Platform::boot();
        Bouncer::tables([
            'permissions'    => 'bouncer_permissions',
            'assigned_roles' => 'bouncer_assigned_roles',
            'roles'          => 'bouncer_roles',
            'abilities'      => 'bouncer_abilities',
        ]);
    }
}