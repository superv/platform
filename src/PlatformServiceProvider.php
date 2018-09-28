<?php

namespace SuperV\Platform;

use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletMakeMigrationCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletRunMigrationCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\Events\DropletInstalledEvent;
use SuperV\Platform\Domains\Droplet\Listeners\DropletInstalledListener;
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
    ];

    protected $aliases = [
        'Platform' => 'SuperV\Platform\Facades\PlatformFacade',
        'Feature'  => 'SuperV\Platform\Domains\Feature\FeatureFacade',
        'Current'  => 'SuperV\Platform\Facades\CurrentFacade',
        'Hub'      => 'SuperV\Platform\Facades\HubFacade',

    ];

    protected $_singletons = [
        'SuperV\Platform\Domains\Auth\Contracts\Users' => 'SuperV\Platform\Domains\Auth\Users',
        'droplets'                                     => DropletCollection::class,
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched'         => 'SuperV\Platform\Listeners\RouteMatchedListener',
        'SuperV\Platform\Domains\Port\PortDetectedEvent' => 'SuperV\Platform\Listeners\PortDetectedListener',
        DropletInstalledEvent::class                     => DropletInstalledListener::class,
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
        $this->mergeConfigFrom(__DIR__.'/../config/superv.php', 'superv');

        $this->bindUserModel();

        $this->enableTwig();

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
        if ($this->app->runningInConsole()) {
            $this->registerMigrationScope();
            $this->publishConfig();
        }

        if (config('superv.installed') !== true) {
            return;
        }

        superv('droplets')->put('superv.platform', Platform::instance());

        Platform::boot();

        $this->registerPlatformRoutes();
    }

    protected function publishConfig(): void
    {
        $stub = __DIR__.'/../config/superv.php';

        $target = $this->app->basePath().'/config/superv.php';

        $this->publishes([$stub => $target], 'superv.config');
    }

    protected function registerMigrationScope(): void
    {
        MigrationScopes::register('platform', realpath(__DIR__.'/../database/migrations'));
    }

    protected function registerPlatformRoutes(): void
    {
        app(Router::class)->loadFromPath(Platform::path('routes'));
    }

    protected function bindUserModel(): void
    {
        $this->_bindings[User::class] = sv_config('auth.user.model');
    }

    protected function enableTwig(): void
    {
        if (sv_config('twig.enabled')) {
            $this->providers[] = TwigServiceProvider::class;
        }
    }
}